@php
    $href = fn ($r) => $r && Route::has($r) ? route($r) : '#';

    // Hide everything the signed-in account has not been granted. Groups carry
    // no permission of their own, so a group disappears when all of its
    // children are hidden.
    $maySee = fn (?string $permission): bool => $permission === null || (auth()->user()?->can($permission) ?? false);

    $menu = collect(config('menu'))
        ->map(function (array $item) use ($maySee): array {
            if (isset($item['children'])) {
                $item['children'] = array_values(array_filter(
                    $item['children'],
                    fn (array $child): bool => $maySee($child['permission'] ?? null),
                ));
            }

            return $item;
        })
        ->filter(fn (array $item): bool => isset($item['children'])
            ? $item['children'] !== []
            : $maySee($item['permission'] ?? null))
        ->values();

    $link = 'flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition';
    $on   = 'admin-primary-soft-bg admin-primary-text';
    $off  = 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800';

    // Brand follows the site name saved in Business Settings, falling back to
    // the application name before the admin has saved one.
    $siteName = trim((string) ($businessAssets['site_name'] ?? '')) ?: config('app.name');
    $siteInitial = mb_strtoupper(mb_substr($siteName, 0, 1));
@endphp

<aside id="admin-sidebar" :class="[mini ? 'lg:w-[72px]' : 'lg:w-64', mobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0']"
       class="fixed inset-y-3 left-3 z-40 flex w-[calc(100%-1.5rem)] flex-col rounded-[10px] border border-gray-200 bg-white shadow-md transition-[width,transform] duration-200
              dark:border-gray-800 dark:bg-gray-900 lg:w-64">

    {{-- Brand --}}
    <div class="flex h-16 shrink-0 items-center gap-2 px-4" :class="mini ? 'justify-center' : 'justify-between'">
        <a href="{{ $href('admin.dashboard') }}" wire:navigate class="flex items-center gap-2 overflow-hidden">
            @if ($businessAssets['logo'] || $businessAssets['logoDark'])
                @if ($businessAssets['logo'])
                    <img src="{{ $businessAssets['logo'] }}" alt="{{ $siteName }} logo"
                         @class(['h-9 w-9 shrink-0 object-contain', 'dark:hidden' => $businessAssets['logoDark']])>
                @endif
                @if ($businessAssets['logoDark'])
                    <img src="{{ $businessAssets['logoDark'] }}" alt="{{ $siteName }} logo"
                         @class(['h-9 w-9 shrink-0 object-contain', 'hidden dark:block' => $businessAssets['logo']])>
                @endif
            @else
                <span class="admin-primary-bg flex h-9 w-9 shrink-0 items-center justify-center rounded-lg font-bold text-white">{{ $siteInitial }}</span>
            @endif
            <span x-show="!mini" class="truncate text-lg font-semibold">{{ $siteName }}</span>
        </a>
    </div>

    {{-- Menu --}}
    <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-2">
        @foreach ($menu as $item)
            @php
                $children = collect($item['children'] ?? [])
                    ->map(fn ($c) => ['label' => $c['label'], 'href' => $href($c['route'] ?? null), 'route' => $c['route'] ?? null]);
                $active = request()->routeIs($item['route'] ?? '__none__')
                    || $children->contains(fn ($c) => $c['route'] && request()->routeIs($c['route']));
                $key = Str::slug($item['label']);
                $flyItems = $children->map(fn ($c) => ['label' => $c['label'], 'href' => $c['href']])->values();
            @endphp

            @if ($children->isEmpty())
                <a href="{{ $href($item['route'] ?? null) }}" wire:navigate
                   @mouseenter="openFly($el, @js($item['label']))" @mouseleave="closeFly()"
                   class="{{ $link }} {{ $active ? $on : $off }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/></svg>
                    <span x-show="!mini" class="truncate">{{ $item['label'] }}</span>
                </a>
            @else
                <div x-init="@if($active) groups['{{ $key }}'] = true @endif">
                    <button type="button"
                            @click="!mini && toggleGroup('{{ $key }}')"
                            @mouseenter="openFly($el, @js($item['label']), @js($flyItems))" @mouseleave="closeFly()"
                            class="{{ $link }} {{ $active ? $on : $off }}">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/></svg>
                        <span x-show="!mini" class="flex-1 truncate text-left">{{ $item['label'] }}</span>
                        <svg x-show="!mini" class="h-4 w-4 shrink-0 transition-transform" :class="groups['{{ $key }}'] && 'rotate-180'"
                             fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                    </button>

                    <div x-cloak x-show="!mini && groups['{{ $key }}']" class="mt-1 space-y-0.5 pl-11">
                        @foreach ($children as $c)
                            <a href="{{ $c['href'] }}" wire:navigate
                               class="block rounded-lg px-3 py-2 text-sm transition
                                      {{ $c['route'] && request()->routeIs($c['route']) ? 'admin-primary-text font-medium' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100' }}">
                                {{ $c['label'] }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    </nav>

    {{-- Online counters pinned to the bottom of the panel --}}
    <livewire:admin.online-users />

</aside>