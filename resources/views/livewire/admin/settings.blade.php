@php
    $canManage = auth()->user()?->can('settings.manage') ?? false;
@endphp

<div class="space-y-6">
    <section class="flex flex-col justify-between gap-5 rounded-md border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:flex-row sm:items-center sm:p-6">
        <div class="space-y-2">
            <div class="flex items-center gap-3">
                <span class="admin-primary-bg flex h-10 w-10 items-center justify-center rounded-md text-white">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 6.75h15m-15 5.25h15m-15 5.25h15M8.25 4.5v15m7.5-15v15" />
                    </svg>
                </span>
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Business settings</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Manage store details and customize the admin and storefront experience.
                    </p>
                </div>
            </div>
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                {{ collect($tabs)->sum('count') }} settings across {{ count($tabs) }} categories
            </p>
        </div>

    </section>

    <x-flash-toast />

    @unless ($canManage)
        <p class="rounded-md border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-600 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300">
            View only — changing these settings needs the <span class="font-mono">settings.manage</span> permission.
        </p>
    @endunless

    <div class="overflow-hidden rounded-md border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <nav aria-label="Settings categories" class="overflow-x-auto border-b border-gray-200 dark:border-gray-800">
            <div class="flex min-w-max gap-1 px-3 pt-2 sm:px-5">
                @foreach ($tabs as $tabId => $tab)
                    <button type="button" wire:key="settings-tab-{{ $tabId }}" wire:click="selectTab('{{ $tabId }}')"
                            aria-current="{{ $activeTab === $tabId ? 'page' : 'false' }}"
                            @class([
                                'inline-flex items-center gap-2 border-b-2 px-3 py-3 text-sm font-medium transition sm:px-4',
                                'admin-primary-text border-current' => $activeTab === $tabId,
                                'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:border-gray-600 dark:hover:text-gray-200' => $activeTab !== $tabId,
                            ])>
                        {{ $tab['label'] }}
                        <span @class([
                            'rounded-full px-2 py-0.5 text-xs',
                            'admin-primary-bg text-white' => $activeTab === $tabId,
                            'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300' => $activeTab !== $tabId,
                        ])>
                            {{ $tab['count'] }}
                        </span>
                    </button>
                @endforeach
            </div>
        </nav>

        <div class="space-y-5 p-4 sm:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $tabs[$activeTab]['label'] }}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $tabs[$activeTab]['description'] }}</p>
                </div>
                @if ($activeTab === 'general')
                    <div class="flex items-center justify-between gap-4 rounded-lg border border-gray-200 px-4 py-3 dark:border-gray-700 sm:min-w-64">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Maintenance mode</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $maintenanceMode ? 'Store is unavailable to customers' : 'Store is open to customers' }}</p>
                        </div>
                        @if ($canManage)
                            <button type="button" role="switch" aria-label="Toggle maintenance mode" aria-checked="{{ $maintenanceMode ? 'true' : 'false' }}"
                                    wire:click="toggleMaintenanceMode"
                                    @class([
                                        'relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors focus:outline-none focus:ring-2 focus:ring-[var(--admin-primary)] focus:ring-offset-2',
                                        'admin-primary-bg' => $maintenanceMode,
                                        'bg-gray-300 dark:bg-gray-700' => ! $maintenanceMode,
                                    ])>
                                <span @class([
                                    'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition',
                                    'translate-x-5' => $maintenanceMode,
                                    'translate-x-0' => ! $maintenanceMode,
                                ])></span>
                            </button>
                        @endif
                    </div>
                @endif
            </div>

            @forelse ($activeSettings as $group => $settings)
                <section wire:key="settings-group-{{ $activeTab }}-{{ Str::slug($group) }}" class="overflow-hidden rounded-md border border-gray-200 dark:border-gray-800">
                    <div class="admin-settings-group-header flex items-center justify-between gap-3 px-4 py-3 sm:px-5">
                        <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $group }}</h4>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ count($settings) }} {{ count($settings) === 1 ? 'setting' : 'settings' }}</span>
                    </div>

                    @if ($group === 'Brand assets')
                        <div class="grid grid-cols-1 gap-4 p-4 md:grid-cols-3">
                            @foreach ($settings as $setting)
                                <article wire:key="brand-asset-{{ $setting['type'] }}"
                                         class="flex flex-col justify-between gap-4 rounded-md border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
                                    <div class="flex items-center gap-4">
                                        <div class="flex h-16 w-20 shrink-0 items-center justify-center rounded-md border border-dashed border-gray-300 bg-gray-50 dark:border-gray-700 dark:bg-gray-800">
                                            @if ($setting['imageUrl'])
                                                <img src="{{ $setting['imageUrl'] }}" alt="{{ $setting['label'] }} preview"
                                                     @class([
                                                         'object-contain',
                                                         'h-10 w-10' => $setting['type'] === 'favicon',
                                                         'h-12 w-16' => $setting['type'] !== 'favicon',
                                                     ])>
                                            @else
                                                <svg class="h-7 w-7 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M7.5 9 12 4.5 16.5 9M12 4.5v12" />
                                                </svg>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <h5 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $setting['label'] }}</h5>
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $setting['displayValue'] }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        @if ($canManage)
                                            <button type="button" wire:click="edit('{{ $setting['type'] }}')"
                                                    aria-label="{{ $setting['exists'] ? 'Change' : 'Upload' }} {{ $setting['label'] }}"
                                                    class="admin-primary-text inline-flex items-center gap-2 rounded-md border border-current/20 px-3 py-2 text-sm font-medium shadow-xs transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-current dark:hover:bg-gray-800">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 2.651 2.651M8 16l8.862-8.862a1.875 1.875 0 112.652 2.652L10.65 18.65a4.5 4.5 0 01-1.897 1.13L5 21l1.22-3.753A4.5 4.5 0 017.35 15.35L8 16z" />
                                                </svg>
                                                {{ $setting['exists'] ? 'Change image' : 'Upload image' }}
                                            </button>
                                        @endif
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @elseif (in_array($group, ['Backend palettes', 'Frontend palettes'], true))
                        <div class="grid grid-cols-1 gap-4 p-4 sm:grid-cols-2 xl:grid-cols-3">
                            @foreach ($settings as $setting)
                                @php($palette = $setting['palette'])
                                <article wire:key="palette-card-{{ $setting['type'] }}"
                                         @class([
                                             'overflow-hidden rounded-md border bg-white shadow-xs transition dark:bg-gray-900',
                                             'border-[var(--admin-primary)] ring-2 ring-[var(--admin-primary)]/20' => $setting['isActive'],
                                             'border-gray-200 hover:-translate-y-0.5 hover:shadow-md dark:border-gray-700' => ! $setting['isActive'],
                                         ])>
                                    <button type="button" wire:click="activatePalette('{{ $setting['type'] }}')"
                                            @disabled(! $canManage || ! $setting['isValidPalette'])
                                            @if ($setting['isActive']) aria-current="true" @endif
                                            class="block w-full text-left focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[var(--admin-primary)] disabled:cursor-not-allowed">
                                        @if ($setting['isValidPalette'])
                                            <div class="flex h-16 w-full" aria-label="Palette color preview">
                                                @foreach (['primary', 'secondary', 'accent', 'bg', 'surface', 'text'] as $colorField)
                                                    @if (! empty($palette[$colorField]) && preg_match('/\A#[0-9A-Fa-f]{6}\z/', (string) $palette[$colorField]))
                                                        <span class="min-w-0 flex-1" title="{{ ucfirst($colorField) }}: {{ $palette[$colorField] }}"
                                                              style="background-color: {{ $palette[$colorField] }}"></span>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="flex h-16 items-center justify-center bg-gray-100 text-sm text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                                                Invalid palette data
                                            </div>
                                        @endif

                                        <div class="flex items-start justify-between gap-3 p-4">
                                            <div class="min-w-0">
                                                <h5 class="truncate text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                    {{ is_array($palette) ? ($palette['name'] ?? 'Unnamed palette') : $setting['label'] }}
                                                </h5>
                                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $setting['label'] }}</p>
                                            </div>
                                            @if ($setting['isActive'])
                                                <span class="admin-primary-soft-bg admin-primary-text inline-flex shrink-0 items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold">
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                                                    </svg>
                                                    Active
                                                </span>
                                            @endif
                                        </div>
                                    </button>

                                    <div class="flex items-center justify-between border-t border-gray-100 px-4 py-3 dark:border-gray-800">
                                        @if (is_array($palette))
                                            <div class="flex items-center gap-1.5" aria-label="Palette colors">
                                                @foreach (['primary', 'secondary', 'accent', 'bg', 'surface', 'text'] as $colorField)
                                                    @if (! empty($palette[$colorField]) && preg_match('/\A#[0-9A-Fa-f]{6}\z/', (string) $palette[$colorField]))
                                                        <span class="h-4 w-4 rounded-full border border-black/10 dark:border-white/10"
                                                              title="{{ ucfirst($colorField) }}: {{ $palette[$colorField] }}"
                                                              style="background-color: {{ $palette[$colorField] }}"></span>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-xs text-red-600">Cannot activate</span>
                                        @endif

                                        <div class="flex items-center gap-1">
                                            @if ($canManage)
                                                <button type="button" wire:click="edit('{{ $setting['type'] }}')"
                                                        aria-label="Edit {{ is_array($palette) ? ($palette['name'] ?? $setting['label']) : $setting['label'] }}"
                                                        title="Edit palette"
                                                        class="admin-primary-text inline-flex h-9 w-9 items-center justify-center rounded-md border border-current/20 shadow-xs transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-current dark:hover:bg-gray-800">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 2.651 2.651M8 16l8.862-8.862a1.875 1.875 0 112.652 2.652L10.65 18.65a4.5 4.5 0 01-1.897 1.13L5 21l1.22-3.753A4.5 4.5 0 017.35 15.35L8 16z" />
                                                    </svg>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @elseif (in_array($group, ['Backend fonts', 'Frontend fonts'], true))
                        <div class="grid grid-cols-1 gap-4 p-4 sm:grid-cols-2 xl:grid-cols-4">
                            @foreach ($settings as $setting)
                                <article wire:key="font-setting-card-{{ $setting['type'] }}" class="flex min-w-0 flex-col gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-xs dark:border-gray-700 dark:bg-gray-900">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="admin-primary-soft-bg admin-primary-text truncate rounded-full px-3 py-1 text-xs font-medium">
                                            {{ str_replace(' font', '', $setting['label']) }}
                                        </span>
                                        @if ($canManage)
                                            <button type="button" wire:click="edit('{{ $setting['type'] }}')"
                                                    aria-label="Edit {{ $setting['label'] }}"
                                                    title="Edit font"
                                                    class="admin-primary-text inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-md border border-current/20 shadow-xs transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-current dark:hover:bg-gray-800">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 2.651 2.651M8 16l8.862-8.862a1.875 1.875 0 112.652 2.652L10.65 18.65a4.5 4.5 0 01-1.897 1.13L5 21l1.22-3.753A4.5 4.5 0 017.35 15.35L8 16z" />
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                    <p aria-hidden="true" class="truncate text-5xl leading-tight text-gray-950 dark:text-gray-50" style="font-family: {{ $setting['fontFamilyCss'] }}">Aa</p>
                                    <div class="min-w-0">
                                        <h5 class="truncate text-sm font-medium text-gray-900 dark:text-gray-100">{{ $setting['displayValue'] }}</h5>
                                        <p class="truncate text-sm text-gray-500 dark:text-gray-400">{{ $setting['fontRoleDescription'] }}</p>
                                    </div>
                                    <div class="truncate border-t border-gray-200 pt-3 text-sm text-gray-600 dark:border-gray-700 dark:text-gray-300" style="font-family: {{ $setting['fontFamilyCss'] }}">
                                        The quick brown fox jumps over the lazy dog.
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @elseif ($group === 'Frontend font sizes')
                        <div class="grid grid-cols-1 gap-4 p-4 sm:grid-cols-2 xl:grid-cols-5">
                            @foreach ($settings as $setting)
                                <article wire:key="font-size-card-{{ $setting['type'] }}" class="flex min-w-0 flex-col gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-xs dark:border-gray-700 dark:bg-gray-900">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="admin-primary-soft-bg admin-primary-text truncate rounded-full px-3 py-1 text-xs font-medium">
                                            {{ str_replace(' font size', '', $setting['label']) }}
                                        </span>
                                        @if ($canManage)
                                            <button type="button" wire:click="edit('{{ $setting['type'] }}')"
                                                    aria-label="Edit {{ $setting['label'] }}"
                                                    title="Edit font size"
                                                    class="admin-primary-text inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-md border border-current/20 shadow-xs transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-current dark:hover:bg-gray-800">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 2.651 2.651M8 16l8.862-8.862a1.875 1.875 0 112.652 2.652L10.65 18.65a4.5 4.5 0 01-1.897 1.13L5 21l1.22-3.753A4.5 4.5 0 017.35 15.35L8 16z" />
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                    <p aria-hidden="true" class="truncate leading-tight text-gray-950 dark:text-gray-50" style="font-size: {{ $setting['fontSizeCss'] ?? '1rem' }}">
                                        {{ $setting['type'] === 'font_size_small' ? 'Small text sample' : 'Aa' }}
                                    </p>
                                    <div class="min-w-0">
                                        <h5 class="truncate text-sm font-medium text-gray-900 dark:text-gray-100">{{ $setting['displayValue'] }}</h5>
                                        <p class="truncate text-sm text-gray-500 dark:text-gray-400">{{ $setting['fontSizeDescription'] }}</p>
                                    </div>
                                    <div class="truncate border-t border-gray-200 pt-3 text-sm text-gray-600 dark:border-gray-700 dark:text-gray-300">
                                        Sample text at the selected size
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @elseif (in_array($group, ['Basic settings', 'Contact & social'], true))
                        <div class="grid grid-cols-1 gap-4 p-4 sm:grid-cols-2 xl:grid-cols-3">
                            @foreach ($settings as $setting)
                                @continue($setting['type'] === 'maintenance_mode')
                                <article wire:key="general-setting-card-{{ $setting['type'] }}" class="flex min-w-0 flex-col gap-4 rounded-xl border border-gray-200 bg-white p-4 shadow-xs dark:border-gray-700 dark:bg-gray-900">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <span class="admin-primary-soft-bg admin-primary-text inline-flex max-w-full truncate rounded-full px-3 py-1 text-xs font-medium">
                                                {{ $group === 'Basic settings' ? 'General' : $group }}
                                            </span>
                                            <h5 class="mt-3 truncate text-sm font-medium text-gray-500 dark:text-gray-400">{{ $setting['label'] }}</h5>
                                        </div>
                                        @if ($canManage)
                                            <button type="button" wire:click="edit('{{ $setting['type'] }}')"
                                                    aria-label="Edit {{ $setting['label'] }}"
                                                    title="Edit setting"
                                                    class="admin-primary-text inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-md border border-current/20 shadow-xs transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-current dark:hover:bg-gray-800">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 2.651 2.651M8 16l8.862-8.862a1.875 1.875 0 112.652 2.652L10.65 18.65a4.5 4.5 0 01-1.897 1.13L5 21l1.22-3.753A4.5 4.5 0 017.35 15.35L8 16z" />
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                    <div class="min-w-0 border-t border-gray-200 pt-3 dark:border-gray-700">
                                        @if ($setting['previewColor'] && preg_match('/\A#[0-9A-Fa-f]{6}\z/', $setting['previewColor']))
                                            <span class="inline-flex items-center gap-2">
                                                <span class="h-6 w-6 rounded-full border border-gray-300 dark:border-gray-600" style="background-color: {{ $setting['previewColor'] }}"></span>
                                                <span class="truncate text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $setting['displayValue'] }}</span>
                                            </span>
                                        @else
                                            <p class="truncate text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $setting['displayValue'] }}</p>
                                        @endif
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @elseif (in_array($group, ['Shipping', 'Payment methods', 'Vodacom Lipa'], true))
                        <div class="grid grid-cols-1 gap-4 p-4 sm:grid-cols-2 xl:grid-cols-3">
                            @foreach ($settings as $setting)
                                <article wire:key="commerce-setting-card-{{ $setting['type'] }}" class="flex min-w-0 flex-col gap-4 rounded-xl border border-gray-200 bg-white p-4 shadow-xs dark:border-gray-700 dark:bg-gray-900">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <span class="admin-primary-soft-bg admin-primary-text inline-flex max-w-full truncate rounded-full px-3 py-1 text-xs font-medium">
                                                {{ match ($group) {
                                                    'Shipping' => 'Shipping',
                                                    'Payment methods' => 'Payment',
                                                    'Vodacom Lipa' => 'Lipa',
                                                    default => $group,
                                                } }}
                                            </span>
                                            <h5 class="mt-3 truncate text-sm font-medium text-gray-500 dark:text-gray-400">{{ $setting['label'] }}</h5>
                                        </div>
                                        @if ($canManage)
                                            <button type="button" wire:click="edit('{{ $setting['type'] }}')"
                                                    aria-label="Edit {{ $setting['label'] }}"
                                                    title="Edit setting"
                                                    class="admin-primary-text inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-md border border-current/20 shadow-xs transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-current dark:hover:bg-gray-800">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 2.651 2.651M8 16l8.862-8.862a1.875 1.875 0 112.652 2.652L10.65 18.65a4.5 4.5 0 01-1.897 1.13L5 21l1.22-3.753A4.5 4.5 0 017.35 15.35L8 16z" />
                                                </svg>
                                            </button>
                                        @endif
                                    </div>

                                    <div class="min-w-0 border-t border-gray-200 pt-3 dark:border-gray-700">
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="min-w-0">
                                                <p @class([
                                                    'truncate text-lg font-semibold',
                                                    'text-gray-900 dark:text-gray-100' => $setting['kind'] !== 'boolean' || $setting['isEnabled'],
                                                    'text-gray-400 dark:text-gray-500' => $setting['kind'] === 'boolean' && ! $setting['isEnabled'],
                                                ])>
                                                    {{ $setting['displayValue'] }}
                                                </p>
                                                <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $setting['hint'] ?? '' }}</p>
                                            </div>
                                            @if ($setting['kind'] === 'boolean' && $canManage)
                                                <button type="button" role="switch" aria-label="Toggle {{ $setting['label'] }}"
                                                        aria-checked="{{ $setting['isEnabled'] ? 'true' : 'false' }}"
                                                        wire:click="toggleBoolean('{{ $setting['type'] }}')"
                                                        @class([
                                                            'relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors focus:outline-none focus:ring-2 focus:ring-[var(--admin-primary)] focus:ring-offset-2',
                                                            'admin-primary-bg' => $setting['isEnabled'],
                                                            'bg-gray-300 dark:bg-gray-700' => ! $setting['isEnabled'],
                                                        ])>
                                                    <span @class([
                                                        'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition',
                                                        'translate-x-5' => $setting['isEnabled'],
                                                        'translate-x-0' => ! $setting['isEnabled'],
                                                    ])></span>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                                <thead class="sr-only">
                                    <tr>
                                        <th scope="col">Setting</th>
                                        <th scope="col">Value</th>
                                        <th scope="col">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                                    @foreach ($settings as $setting)
                                        <tr wire:key="business-setting-{{ $setting['type'] }}" class="transition hover:bg-gray-50/70 dark:hover:bg-gray-800/30">
                                            <td class="min-w-48 px-4 py-4 text-sm font-medium text-gray-900 dark:text-gray-100 sm:px-5">
                                                {{ $setting['label'] }}
                                            </td>
                                            <td class="min-w-40 px-4 py-4 text-sm text-gray-600 dark:text-gray-300 sm:px-5">
                                                @if ($setting['kind'] === 'image')
                                                    <span class="inline-flex items-center gap-3">
                                                        @if ($setting['imageUrl'])
                                                            <img src="{{ $setting['imageUrl'] }}" alt="{{ $setting['label'] }} preview"
                                                                 @class([
                                                                     'rounded object-contain',
                                                                     'h-8 w-8' => $setting['type'] === 'favicon',
                                                                     'h-10 w-24' => $setting['type'] !== 'favicon',
                                                                 ])>
                                                        @endif
                                                        <span>{{ $setting['displayValue'] }}</span>
                                                    </span>
                                                @elseif ($setting['previewColor'] && preg_match('/\A#[0-9A-Fa-f]{6}\z/', $setting['previewColor']))
                                                    <span class="inline-flex items-center gap-2">
                                                        <span class="h-5 w-5 rounded border border-gray-300 dark:border-gray-600"
                                                              style="background-color: {{ $setting['previewColor'] }}"></span>
                                                        <span>{{ $setting['displayValue'] }}</span>
                                                        @if ($setting['kind'] === 'color')
                                                            <span class="font-mono text-xs">{{ $setting['previewColor'] }}</span>
                                                        @endif
                                                    </span>
                                                @else
                                                    {{ $setting['displayValue'] }}
                                                @endif
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-4 text-right text-sm sm:px-5">
                                                @if ($canManage)
                                                    <button type="button" wire:click="edit('{{ $setting['type'] }}')"
                                                            aria-label="Edit {{ $setting['label'] }}"
                                                            title="Edit setting"
                                                            class="admin-primary-text inline-flex h-9 w-9 items-center justify-center rounded-md border border-current/20 shadow-xs transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-current dark:hover:bg-gray-800">
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 2.651 2.651M8 16l8.862-8.862a1.875 1.875 0 112.652 2.652L10.65 18.65a4.5 4.5 0 01-1.897 1.13L5 21l1.22-3.753A4.5 4.5 0 017.35 15.35L8 16z" />
                                                        </svg>
                                                    </button>
                                                @else
                                                    <span class="text-xs text-gray-400 dark:text-gray-500">View only</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </section>
            @empty
                <div class="rounded-md border border-dashed border-gray-300 px-6 py-10 text-center dark:border-gray-700">
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-200">No settings in this category yet.</p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Add a setting to get started.</p>
                </div>
            @endforelse
        </div>
    </div>

    @if ($canManage && $isOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto p-4" role="dialog" aria-modal="true" aria-labelledby="setting-modal-title">
            <button type="button" class="fixed inset-0 bg-black/50" wire:click="$set('isOpen', false)" aria-label="Close dialog"></button>

            <div class="relative my-auto max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-md bg-white p-6 shadow-xl dark:bg-gray-900">
                <h3 id="setting-modal-title" class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    {{ $settingKind === 'image'
                        ? ($editingType ? 'Change ' : 'Upload ').$settingLabel
                        : 'Edit setting' }}
                </h3>

                <form wire:submit="save" class="mt-5 space-y-4">
                    <div>
                        <label for="setting-type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Setting</label>
                        <p class="mt-1 rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-700 dark:border-gray-700 dark:text-gray-300">
                            {{ $settingLabel }}
                        </p>
                    </div>

                    @if ($settingKind === 'palette')
                        @if ($paletteDataInvalid)
                            <p class="rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800 dark:border-amber-800 dark:bg-amber-950 dark:text-amber-200">
                                The stored palette value is not valid JSON. Enter a complete palette below to replace it.
                            </p>
                        @endif

                        <div>
                            <label for="palette-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Palette name</label>
                            <input id="palette-name" type="text" wire:model="palette.name"
                                   class="admin-primary-focus mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            @error('palette.name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            @foreach ([
                                'primary' => 'Primary',
                                'secondary' => 'Secondary',
                                'accent' => 'Accent',
                                'bg' => 'Background',
                                'surface' => 'Surface',
                                'text' => 'Text',
                                'bg_dark' => 'Dark background',
                                'surface_dark' => 'Dark surface',
                                'text_dark' => 'Dark text',
                            ] as $field => $label)
                                <div wire:key="palette-field-{{ $field }}">
                                    <label for="palette-{{ $field }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label }}</label>
                                    <div class="mt-1 flex items-center gap-3">
                                        <input id="palette-{{ $field }}" type="color" wire:model="palette.{{ $field }}"
                                               class="admin-primary-focus block h-10 w-14 rounded-md border-gray-300 p-1 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                                        <span class="font-mono text-sm text-gray-600 dark:text-gray-300">{{ $palette[$field] ?? '' }}</span>
                                    </div>
                                    @error('palette.'.$field) <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            @endforeach
                        </div>
                    @elseif ($settingKind === 'boolean')
                        <div>
                            <label for="setting-value" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                            <select id="setting-value" wire:model="value" class="admin-primary-focus mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                                <option value="1">Enabled</option>
                                <option value="0">Disabled</option>
                            </select>
                            @error('value') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    @elseif ($settingKind === 'palette_selection')
                        <div>
                            <label for="setting-value" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Active palette</label>
                            <select id="setting-value" wire:model="value" class="admin-primary-focus mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                                @foreach (range(1, 4) as $paletteNumber)
                                    <option value="{{ $paletteNumber }}">Palette {{ $paletteNumber }}</option>
                                @endforeach
                            </select>
                            @error('value') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    @elseif ($settingKind === 'color')
                        <div>
                            <label for="setting-value" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Color</label>
                            <input id="setting-value" type="color" wire:model="value"
                                   class="admin-primary-focus mt-1 block h-11 w-full rounded-md border-gray-300 p-1 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            @error('value') <p class="mt-1 text-sm text-red-600">Choose a valid 6-digit hex color.</p> @enderror
                        </div>
                    @elseif (in_array($settingKind, ['font', 'admin_font'], true))
                        <div>
                            <label for="setting-value" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Font family</label>
                            <select id="setting-value" wire:model="value" class="admin-primary-focus mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                                @foreach ($settingKind === 'admin_font' ? ['Inter', 'Arial', 'Georgia', 'System'] : ['Inter', 'Poppins', 'Playfair Display', 'Plus Jakarta Sans', 'Arial', 'Georgia', 'System'] as $fontFamily)
                                    <option value="{{ $fontFamily }}">{{ $fontFamily }}</option>
                                @endforeach
                            </select>
                            @error('value') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    @elseif ($settingKind === 'image')
                        <div class="space-y-3">
                            @if ($upload)
                                <img src="{{ $upload->temporaryUrl() }}" alt="Selected image preview"
                                     @class([
                                         'rounded border border-gray-200 object-contain dark:border-gray-700',
                                         'h-16 w-16' => $settingType === 'favicon',
                                         'h-24 w-48' => $settingType !== 'favicon',
                                     ])>
                            @elseif (($pickedMediaUrls['branding'][0] ?? null) !== null)
                                <img src="{{ $pickedMediaUrls['branding'][0] }}" alt="Picture picked from the media library"
                                     @class([
                                         'rounded border border-gray-200 object-contain dark:border-gray-700',
                                         'h-16 w-16' => $settingType === 'favicon',
                                         'h-24 w-48' => $settingType !== 'favicon',
                                     ])>
                            @elseif ($currentImageUrl)
                                <img src="{{ $currentImageUrl }}" alt="Current image"
                                     @class([
                                         'rounded border border-gray-200 object-contain dark:border-gray-700',
                                         'h-16 w-16' => $settingType === 'favicon',
                                         'h-24 w-48' => $settingType !== 'favicon',
                                     ])>
                            @endif

                            <div>
                                <label for="business-image" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ $editingType ? 'Choose replacement image' : 'Choose image' }}
                                </label>
                                <input id="business-image" type="file" wire:model="upload"
                                       accept="{{ $settingType === 'favicon' ? 'image/png' : 'image/jpeg,image/png,image/webp' }}"
                                       class="mt-1 block w-full rounded-md border border-gray-300 text-sm text-gray-700 file:mr-4 file:border-0 file:bg-gray-100 file:px-4 file:py-2 dark:border-gray-700 dark:text-gray-300 dark:file:bg-gray-800">
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    PNG, JPG, or WebP up to 2 MB. Favicons must be square PNG images between 16 and 512 pixels.
                                </p>
                                @error('upload') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            @if (auth()->user()?->can('media.view'))
                                <div class="flex flex-wrap items-center gap-2">
                                    <button type="button" wire:click="openMediaPicker('branding')"
                                            class="rounded-md border border-gray-300 px-3 py-2 text-xs font-semibold text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800">
                                        Choose from media
                                    </button>
                                    @if (($pickedMediaUrls['branding'][0] ?? null) !== null)
                                        <button type="button" wire:click="clearPickedMedia('branding')"
                                                class="rounded-md border border-gray-300 px-3 py-2 text-xs font-semibold text-gray-500 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800">
                                            Clear picked image
                                        </button>
                                    @endif
                                </div>
                            @endif

                            <p wire:loading wire:target="upload" class="text-sm text-gray-500 dark:text-gray-400">Uploading image…</p>
                        </div>
                    @elseif ($settingKind === 'textarea')
                        <div>
                            <label for="setting-value" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Value</label>
                            <div class="mt-1">
                                <x-rich-editor
                                    id="setting-value"
                                    wire:model="value"
                                    :placeholder="$settingType === 'footer_about'
                                        ? 'Share a short company overview for the storefront footer...'
                                        : 'Explain how customers should complete Vodacom Lipa payment...'"
                                />
                            </div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                {{ $settingType === 'footer_about' ? 'Shown below the logo on the left side of the storefront footer.' : 'Shown to the customer on the checkout page and their order.' }}
                            </p>
                            @error('value') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    @elseif ($settingType)
                        <div>
                            <label for="setting-value" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ $settingKind === 'font_size' ? 'Font size' : 'Value' }}</label>
                            <input id="setting-value" type="{{ match ($settingKind) { 'secret' => 'password', 'email' => 'email', 'url' => 'url', 'phone' => 'tel', default => 'text' } }}" wire:model="value" autocomplete="off"
                                   @if (in_array($settingKind, ['country', 'palette_selection', 'lipa_number'], true)) inputmode="numeric" @endif
                                   @if ($settingKind === 'decimal') inputmode="decimal" @endif
                                   @if ($settingKind === 'phone') inputmode="tel" @endif
                                   class="admin-primary-focus mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            @if ($settingKind === 'font_size')
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Use a CSS size such as 16px, 1rem, 1.25em, or 100%.</p>
                            @elseif ($settingKind === 'lipa_number')
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">The 5 to 9 digit merchant number customers pay into. Lipa is only offered at checkout when this is set.</p>
                            @elseif ($settingKind === 'secret')
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Used to verify the Vodacom callback signature. Leave empty to keep the callback disabled.</p>
                            @elseif ($settingKind === 'email')
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Displayed on the left side of the top bar as a mailto link. Leave empty to hide.</p>
                            @elseif ($settingKind === 'phone')
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Displayed on the left side of the top bar as a clickable phone link. Leave empty to hide.</p>
                            @elseif ($settingKind === 'whatsapp')
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Shown as a floating WhatsApp icon at the bottom of storefront pages (enter a phone number with country code or a https://wa.me link). Leave empty to hide.</p>
                            @elseif ($settingKind === 'url')
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Full https:// link shown below the About Us content in the storefront footer. Leave empty to hide.</p>
                            @endif
                            @error('value') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="$set('isOpen', false)"
                                class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                            Cancel
                        </button>
                        <button type="submit"
                                class="admin-primary-bg rounded-md px-4 py-2 text-sm font-medium text-white">
                            {{ $settingKind === 'image' ? ($editingType || ($pickedMediaUrls['branding'][0] ?? null) !== null ? 'Save image' : 'Upload image') : 'Save setting' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if ($mediaPickerOpen && auth()->user()?->can('media.view'))
        <livewire:admin.media-picker />
    @endif
</div>
