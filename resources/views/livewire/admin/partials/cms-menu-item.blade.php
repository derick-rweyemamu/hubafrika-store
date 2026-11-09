<article wire:key="cms-menu-item-{{ $item->id }}" class="space-y-2 rounded-xl border border-gray-200 bg-white p-3.5 shadow-sm transition dark:border-gray-800 dark:bg-gray-900/60 {{ $depth > 0 ? 'ml-6 border-l-4 border-l-indigo-400 dark:border-l-indigo-600' : '' }}">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="min-w-0 flex-1">
            <div class="flex items-center gap-2">
                <p class="truncate text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $item->label }}</p>
                @if ($item->badge)
                    <span class="rounded bg-indigo-100 px-2 py-0.5 text-[11px] font-semibold text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300">
                        {{ $item->badge }}
                    </span>
                @endif
                @if ($item->is_mega_menu)
                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-medium text-amber-800 dark:bg-amber-950 dark:text-amber-300">
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                        Mega menu ({{ $item->mega_menu_columns }} cols)
                    </span>
                @endif
                @if ($item->mega_menu_column)
                    <span class="rounded bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                        Col {{ $item->mega_menu_column }}
                    </span>
                @endif
            </div>

            <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                <span>
                    @if ($item->page)
                        <span class="font-medium text-gray-700 dark:text-gray-300">Page:</span> {{ $item->page->title }}
                    @elseif ($item->post)
                        <span class="font-medium text-gray-700 dark:text-gray-300">Post:</span> {{ $item->post->title }}
                    @elseif ($item->url)
                        <span class="font-medium text-gray-700 dark:text-gray-300">Link:</span> <span class="font-mono text-[11px]">{{ $item->url }}</span>
                    @else
                        <span>Dropdown category</span>
                    @endif
                </span>
                @if ($item->css_class)
                    <span class="font-mono text-[10px] text-gray-400">.{{ $item->css_class }}</span>
                @endif
            </div>
        </div>

        <div class="flex shrink-0 items-center gap-1.5">
            @if ($item->is_mega_menu && $depth === 0)
                <button type="button" wire:click="openMegaDesigner({{ $item->id }})" class="inline-flex items-center gap-1 rounded-lg border border-amber-300 bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-800 hover:bg-amber-100 dark:border-amber-700 dark:bg-amber-950/60 dark:text-amber-200">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                    Design columns
                </button>
            @endif

            <button type="button" wire:click="editItem({{ $item->id }})" class="rounded-lg border border-gray-300 px-2.5 py-1 text-xs font-medium hover:bg-gray-100 dark:border-gray-700 dark:hover:bg-gray-800">
                Edit
            </button>
            <button type="button" aria-label="Move {{ $item->label }} up" wire:click="moveItem({{ $item->id }}, 'up')" @disabled($index === 0) class="rounded-lg border border-gray-300 px-2 py-1 text-sm disabled:opacity-30 hover:bg-gray-100 dark:border-gray-700 dark:hover:bg-gray-800">
                ↑
            </button>
            <button type="button" aria-label="Move {{ $item->label }} down" wire:click="moveItem({{ $item->id }}, 'down')" @disabled($index === $siblingsCount - 1) class="rounded-lg border border-gray-300 px-2 py-1 text-sm disabled:opacity-30 hover:bg-gray-100 dark:border-gray-700 dark:hover:bg-gray-800">
                ↓
            </button>
            <button type="button" wire:click="removeItem({{ $item->id }})" wire:confirm="Remove this menu item and its nested items?" class="rounded-lg px-2 py-1 text-xs font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-950/50">
                Remove
            </button>
        </div>
    </div>

    @if ($item->children->isNotEmpty())
        <div class="space-y-2 pt-1">
            @foreach ($item->children as $childIndex => $child)
                @include('livewire.admin.partials.cms-menu-item', [
                    'item' => $child,
                    'index' => $childIndex,
                    'siblingsCount' => $item->children->count(),
                    'depth' => $depth + 1,
                ])
            @endforeach
        </div>
    @endif
</article>
