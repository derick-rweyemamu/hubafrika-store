<div class="space-y-6">
    <x-flash-toast />

    <!-- TAB: NAVIGATION MENUS & MENU BUILDER (Matches School Project Structure) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Left: Menus List (4 Cols) -->
        <div class="lg:col-span-4 space-y-3">
            <div class="flex items-center justify-between pb-1">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-2">
                    <i class="fas fa-bars text-violet-500"></i> {{ __('Defined Menus') }}
                </span>
                <button wire:click="openCreateMenu"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-violet-600 hover:bg-violet-500 text-white font-bold text-xs shadow-xs transition cursor-pointer">
                    <i class="fas fa-plus text-[10px]"></i>
                    <span>{{ __('Add Menu') }}</span>
                </button>
            </div>

            <div class="space-y-2">
                @forelse($menus as $m)
                <div class="p-4 rounded-2xl border transition-all cursor-pointer {{ $selectedMenuId === $m->id ? 'bg-violet-50/80 dark:bg-violet-950/40 border-violet-400 dark:border-violet-600 ring-2 ring-violet-500/20' : 'bg-white dark:bg-slate-900 border-slate-200/80 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700' }}"
                    wire:click="selectMenu({{ $m->id }})">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-sm text-slate-900 dark:text-white">{{ $m->title }}</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase {{ $m->location === 'header' ? 'bg-sky-100 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300' }}">
                                    {{ $m->location ?: 'header' }}
                                </span>
                            </div>
                            <div class="text-[11px] font-mono text-slate-400 mt-0.5">slug: {{ $m->slug }}</div>
                        </div>

                        <div class="flex items-center gap-1">
                            <button wire:click.stop="editMenu({{ $m->id }})"
                                class="h-7 w-7 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center justify-center transition cursor-pointer"
                                title="{{ __('Edit Menu Details') }}">
                                <i class="fas fa-cog text-[11px]"></i>
                            </button>
                            <button wire:click.stop="deleteMenu({{ $m->id }})" wire:confirm="{{ __('Are you sure you want to delete this menu and all its links?') }}"
                                class="h-7 w-7 rounded-lg bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/40 text-rose-600 dark:text-rose-400 flex items-center justify-center transition cursor-pointer"
                                title="{{ __('Delete Menu') }}">
                                <i class="fas fa-trash-alt text-[11px]"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mt-3 flex items-center justify-between text-xs pt-2 border-t border-slate-100 dark:border-slate-800/80 text-slate-500 dark:text-slate-400">
                        <span><i class="fas fa-link text-[10px] mr-1 text-slate-400"></i> {{ $m->items_count }} {{ __('links') }}</span>
                        <span class="text-[10px] font-semibold {{ ($m->status ?? 1) ? 'text-emerald-500' : 'text-slate-400' }}">{{ ($m->status ?? 1) ? __('Active') : __('Inactive') }}</span>
                    </div>
                </div>
                @empty
                <div class="p-8 text-center rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs text-slate-400">
                    {{ __('No menus configured yet.') }}
                </div>
                @endforelse
            </div>
        </div>

        <!-- Right: Menu Item Builder (8 Cols - Exactly matches User Screenshot) -->
        <div class="lg:col-span-8 space-y-4">
            @if($selectedMenu)
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6 space-y-5">
                <!-- Top Header Row -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg font-extrabold text-slate-900 dark:text-white">{{ $selectedMenu->title }}</h3>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $selectedMenu->location === 'footer' ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/50 dark:text-amber-300' : 'bg-violet-100 text-violet-700 dark:bg-violet-950/50 dark:text-violet-300' }}">
                                {{ ucfirst($selectedMenu->location ?: 'Header') }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-400 mt-0.5">{{ __('Reorder, add, or customize navigation links.') }}</p>
                    </div>

                    <button wire:click="openCreateMenuItem"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-violet-600 hover:bg-violet-500 text-white font-bold text-xs shadow-xs transition cursor-pointer">
                        <i class="fas fa-plus text-xs"></i>
                        <span>{{ $selectedMenu->location === 'footer' ? __('Add Column / Link') : __('Add Navigation Link') }}</span>
                    </button>
                </div>

                @if($selectedMenu->location === 'footer')
                <!-- Informational Banner for Footer Menu -->
                <div class="p-3.5 rounded-2xl bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-950/30 dark:to-orange-950/20 border border-amber-200/80 dark:border-amber-800/60 flex items-start gap-3">
                    <div class="h-8 w-8 rounded-xl bg-amber-500/15 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 text-sm">
                        <i class="fas fa-columns"></i>
                    </div>
                    <div class="text-xs">
                        <div class="font-bold text-amber-900 dark:text-amber-200">{{ __('Footer Columns & Links Structure') }}</div>
                        <p class="text-amber-700/90 dark:text-amber-300/80 mt-0.5 leading-relaxed">
                            {{ __('Top-level items represent the column headers (e.g. "Shop Categories", "Customer Care", "Get in touch"). Links added as children under each column header will automatically display in that column on the storefront footer.') }}
                        </p>
                        <p class="text-amber-700/90 dark:text-amber-300/80 mt-0.5 leading-relaxed">
                            {{ __('Top-level items without children appear as the small links beside the copyright line, so use them for Terms, Privacy and Cookie policies. Phone links (tel:) and email links (mailto:) get their own icon, and links to a social network (Facebook, Instagram, X, WhatsApp, YouTube, LinkedIn, TikTok) become the social buttons at the top of the footer.') }}
                        </p>
                    </div>
                </div>
                @endif

                <!-- Links List (Hierarchical Menu & Mega Menu Builder) -->
                <div class="space-y-3">
                    @forelse($activeMenuItems as $index => $item)
                    <div class="space-y-2">
                        <!-- Top-Level Root Item Row (Matches User Screenshot) -->
                        <div class="p-3.5 px-4 rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200/90 dark:border-slate-700/80 flex items-center justify-between gap-3 hover:border-violet-300 dark:hover:border-violet-700 transition">
                            <div class="flex items-center gap-3 min-w-0">
                                <!-- Reorder Buttons -->
                                <div class="flex flex-col gap-0.5 shrink-0 text-slate-400">
                                    <button type="button" wire:click="moveMenuItem({{ $item->id }}, 'up')"
                                        class="h-4 w-5 flex items-center justify-center hover:text-violet-600 cursor-pointer {{ $loop->first ? 'opacity-30 pointer-events-none' : '' }}" title="{{ __('Move Up') }}">
                                        <i class="fas fa-chevron-up text-[9px]"></i>
                                    </button>
                                    <button type="button" wire:click="moveMenuItem({{ $item->id }}, 'down')"
                                        class="h-4 w-5 flex items-center justify-center hover:text-violet-600 cursor-pointer {{ $loop->last ? 'opacity-30 pointer-events-none' : '' }}" title="{{ __('Move Down') }}">
                                        <i class="fas fa-chevron-down text-[9px]"></i>
                                    </button>
                                </div>

                                <!-- Icon Preview -->
                                <div class="h-9 w-9 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-violet-500 shrink-0">
                                    <i class="{{ $item->icon ?: 'fas fa-link' }} text-xs"></i>
                                </div>

                                <!-- Title, Meta & Badges -->
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ $item->title }}</span>

                                        <!-- Type Tag -->
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-mono uppercase {{ $item->type === 'page' ? 'bg-sky-100 text-sky-700 dark:bg-sky-950 dark:text-sky-300' : ($item->type === 'post' ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300' : ($item->type === 'category' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-300')) }}">
                                            {{ $item->type ?: 'custom' }}
                                        </span>

                                        <!-- Mega Menu Badge -->
                                        @if($item->is_mega_menu)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/15 text-amber-700 dark:text-amber-300 border border-amber-500/30">
                                            <i class="fas fa-th-large text-[9px]"></i>
                                            <span>{{ __('Mega Menu') }} ({{ $item->mega_menu_columns }} cols)</span>
                                        </span>
                                        @endif

                                        <!-- Custom Badge -->
                                        @if($item->badge_text)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold"
                                            style="background-color: {{ $item->badge_color ?? '#ef4444' }}22; color: {{ $item->badge_color ?? '#ef4444' }}; border: 1px solid {{ $item->badge_color ?? '#ef4444' }}44;">
                                            {{ $item->badge_text }}
                                        </span>
                                        @endif

                                        <!-- Child count badge -->
                                        @if($item->children->count() > 0)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-200/80 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                                            <i class="fas fa-sitemap text-[9px] mr-1 text-slate-400"></i>{{ $item->children->count() }} {{ __('sub-items') }}
                                        </span>
                                        @endif
                                    </div>

                                    @if($item->description)
                                    <div class="text-xs text-slate-500 dark:text-slate-400 truncate mt-0.5 italic">
                                        {{ $item->description }}
                                    </div>
                                    @endif

                                    <div class="text-[11px] font-mono text-slate-400 truncate mt-0.5">
                                        {{ $item->resolved_url }}
                                        @if($item->target === '_blank')
                                        <span class="text-[10px] text-amber-500 ml-1 font-sans"><i class="fas fa-external-link-alt text-[9px]"></i> new tab</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Actions Row (Manage Mega Menu, Live Preview, Mega On, Add Link, Edit, Delete) -->
                            <div class="flex items-center gap-1.5 shrink-0">
                                @if($item->is_mega_menu)
                                <!-- Manage Mega Menu Multi-Column Builder (Matches User Screenshot) -->
                                <button type="button" wire:click="openMegaBuilder({{ $item->id }})"
                                    class="h-8 px-3 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white text-xs font-black flex items-center gap-1.5 shadow-xs transition cursor-pointer"
                                    title="{{ __('Manage Multi-Column Mega Menu Builder') }}">
                                    <i class="fas fa-columns text-[10px]"></i>
                                    <span>{{ __('Manage Mega Menu') }}</span>
                                </button>

                                <!-- Live Preview Mega Menu -->
                                <button type="button" wire:click="openMegaPreview({{ $item->id }})"
                                    class="h-8 px-2.5 rounded-xl bg-violet-50 hover:bg-violet-100 dark:bg-violet-950/40 dark:hover:bg-violet-900/60 text-violet-700 dark:text-violet-300 border border-violet-200 dark:border-violet-800 text-xs font-bold flex items-center gap-1.5 transition cursor-pointer"
                                    title="{{ __('Preview Mega Menu Layout') }}">
                                    <i class="fas fa-eye text-[10px]"></i>
                                    <span class="hidden sm:inline">{{ __('Live Preview') }}</span>
                                </button>
                                @endif

                                <!-- Mega Menu Quick Toggle -->
                                <button type="button" wire:click="toggleMegaMenu({{ $item->id }})"
                                    class="h-8 px-2.5 rounded-xl border text-xs font-semibold flex items-center gap-1.5 transition cursor-pointer {{ $item->is_mega_menu ? 'bg-amber-500/10 border-amber-500/40 text-amber-700 dark:text-amber-300 hover:bg-amber-500/20' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:text-amber-600 hover:border-amber-300' }}"
                                    title="{{ $item->is_mega_menu ? __('Disable Mega Menu') : __('Convert to Mega Menu') }}">
                                    <i class="fas fa-th-large text-[10px]"></i>
                                    <span class="hidden xl:inline">{{ $item->is_mega_menu ? __('Mega On') : __('Mega Menu') }}</span>
                                </button>

                                <!-- + Add Link button -->
                                <button type="button" wire:click="openCreateMenuItem({{ $item->id }})"
                                    class="h-8 px-2.5 rounded-xl bg-sky-50 hover:bg-sky-100 dark:bg-sky-950/40 dark:hover:bg-sky-900/60 text-sky-700 dark:text-sky-300 border border-sky-200 dark:border-sky-800 text-xs font-bold flex items-center gap-1.5 transition cursor-pointer"
                                    title="{{ __('Add single item or link') }}">
                                    <i class="fas fa-plus text-[10px]"></i>
                                    <span class="hidden sm:inline">{{ __('Add Link') }}</span>
                                </button>

                                <button wire:click="editMenuItem({{ $item->id }})"
                                    class="h-8 w-8 rounded-xl bg-white dark:bg-slate-900 hover:bg-violet-50 dark:hover:bg-violet-950/40 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 flex items-center justify-center transition cursor-pointer"
                                    title="{{ __('Edit Link') }}">
                                    <i class="fas fa-edit text-xs"></i>
                                </button>
                                <button wire:click="deleteMenuItem({{ $item->id }})" wire:confirm="{{ __('Are you sure you want to delete this link?') }}"
                                    class="h-8 w-8 rounded-xl bg-white dark:bg-slate-900 hover:bg-rose-50 dark:hover:bg-rose-950/40 border border-slate-200 dark:border-slate-700 text-rose-600 dark:text-rose-400 flex items-center justify-center transition cursor-pointer"
                                    title="{{ __('Delete Link') }}">
                                    <i class="fas fa-trash-alt text-xs"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Mega Menu Column-Organized Layout Board (EXACT MATCH to Screenshot) -->
                        @if($item->is_mega_menu)
                        <div class="ml-4 p-4 rounded-2xl bg-slate-100/60 dark:bg-slate-950/50 border border-slate-200 dark:border-slate-800 space-y-3">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center gap-2">
                                    <i class="fas fa-th-large text-amber-500"></i>
                                    <span>{{ __('Mega Menu Multi-Column Content') }}</span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-mono bg-amber-500/15 text-amber-700 dark:text-amber-300 font-bold border border-amber-500/30">
                                        {{ min((int)$item->mega_menu_columns, 4) }} {{ __('Columns Configured (Max 4)') }}
                                    </span>
                                </span>
                                <div class="flex items-center gap-2">
                                    <button type="button" wire:click="openMegaBuilder({{ $item->id }})"
                                        class="text-xs font-bold text-amber-600 dark:text-amber-400 hover:underline flex items-center gap-1 cursor-pointer">
                                        <i class="fas fa-columns text-[11px]"></i> {{ __('Open Multi-Column Builder') }}
                                    </button>
                                    <span class="text-slate-300 dark:text-slate-700">•</span>
                                    <button type="button" wire:click="openMegaPreview({{ $item->id }})"
                                        class="text-xs font-bold text-violet-600 dark:text-violet-400 hover:underline flex items-center gap-1 cursor-pointer">
                                        <i class="fas fa-eye text-[11px]"></i> {{ __('Preview Menu Grid') }}
                                    </button>
                                </div>
                            </div>

                            <!-- Columns Grid (Max 4 Columns side-by-side) -->
                            <div class="grid grid-cols-1 md:grid-cols-{{ min((int)$item->mega_menu_columns, 4) }} gap-3">
                                @for($col = 1; $col <= min((int)$item->mega_menu_columns, 4); $col++)
                                @php
                                    $colItems = $item->children->filter(fn($c) => (int)($c->mega_column ?: ($c->mega_menu_column ?: 1)) === $col);
                                @endphp
                                <div class="p-3 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 flex flex-col justify-between shadow-2xs">
                                    <div class="space-y-2.5">
                                        <!-- Column Header -->
                                        <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
                                            <span class="text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                                                <i class="fas fa-columns text-amber-500 text-[10px]"></i>
                                                {{ __('Column') }} {{ $col }}
                                                <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">{{ $colItems->count() }}</span>
                                            </span>
                                            <button type="button" wire:click="openCreateMenuItem({{ $item->id }}, {{ $col }})"
                                                class="h-6 px-2 rounded-lg bg-amber-500 hover:bg-amber-600 text-white text-[10px] font-bold flex items-center gap-1 transition cursor-pointer"
                                                title="{{ __('Add item to Column') }} {{ $col }}">
                                                <i class="fas fa-plus text-[8px]"></i>
                                                <span>{{ __('Add') }}</span>
                                            </button>
                                        </div>

                                        <!-- Items List in this Column -->
                                        <div class="space-y-2">
                                            @forelse($colItems as $child)
                                            <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80 hover:border-amber-400 transition">
                                                @if($child->type === 'image' && $child->megaMenuImageUrl())
                                                <!-- Image Menu Thumbnail -->
                                                <div class="relative rounded-lg overflow-hidden mb-2 max-h-24 bg-slate-200 dark:bg-slate-700">
                                                    <img src="{{ $child->megaMenuImageUrl() }}" alt="" class="w-full h-20 object-cover">
                                                    <span class="absolute top-1 right-1 px-1.5 py-0.5 rounded text-[8px] font-bold uppercase bg-slate-950/75 text-white backdrop-blur-xs flex items-center gap-1">
                                                        <i class="fas fa-image text-[8px]"></i> {{ __('Image Card') }}
                                                    </span>
                                                </div>
                                                @endif

                                                <div class="flex items-start justify-between gap-1.5">
                                                    <div class="min-w-0 flex-1">
                                                        <div class="flex items-center gap-1 flex-wrap">
                                                            <span class="text-xs font-bold text-slate-900 dark:text-white truncate">{{ $child->title }}</span>
                                                            <span class="px-1.5 py-0.2 rounded text-[9px] font-mono uppercase {{ $child->type === 'image' ? 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300' : ($child->type === 'html' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-300') }}">
                                                                {{ $child->type ?: 'custom' }}
                                                            </span>
                                                            @if($child->badge_text)
                                                            <span class="px-1.5 py-0.2 rounded text-[9px] font-bold"
                                                                style="background-color: {{ $child->badge_color ?? '#ef4444' }}22; color: {{ $child->badge_color ?? '#ef4444' }};">
                                                                {{ $child->badge_text }}
                                                            </span>
                                                            @endif
                                                        </div>

                                                        @if($child->description)
                                                        <div class="text-[11px] text-slate-500 dark:text-slate-400 truncate mt-0.5">{{ $child->description }}</div>
                                                        @endif

                                                        @if($child->resolved_url && $child->resolved_url !== '#')
                                                        <div class="text-[10px] font-mono text-slate-400 truncate mt-0.5">{{ $child->resolved_url }}</div>
                                                        @endif
                                                    </div>

                                                    <!-- Actions -->
                                                    <div class="flex items-center gap-0.5 shrink-0 pt-0.5">
                                                        <button wire:click="editMenuItem({{ $child->id }})"
                                                            class="h-6 w-6 rounded-md hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center justify-center transition cursor-pointer"
                                                            title="{{ __('Edit') }}">
                                                            <i class="fas fa-edit text-[10px]"></i>
                                                        </button>
                                                        <button wire:click="deleteMenuItem({{ $child->id }})" wire:confirm="{{ __('Delete this link?') }}"
                                                            class="h-6 w-6 rounded-md hover:bg-rose-100 dark:hover:bg-rose-950 text-rose-600 dark:text-rose-400 flex items-center justify-center transition cursor-pointer"
                                                            title="{{ __('Delete') }}">
                                                            <i class="fas fa-trash-alt text-[10px]"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            @empty
                                            <!-- Empty Box matching Screenshot -->
                                            <div class="py-5 text-center rounded-xl border border-dashed border-slate-200 dark:border-slate-800 text-xs text-slate-400">
                                                <i class="fas fa-columns text-slate-300 dark:text-slate-700 block mb-1"></i>
                                                {{ __('Column') }} {{ $col }} {{ __('is empty') }}
                                            </div>
                                            @endforelse
                                        </div>
                                    </div>

                                    <!-- Bottom Quick Add Button (Matches Screenshot) -->
                                    <div class="pt-2 mt-2 border-t border-slate-100 dark:border-slate-800/60">
                                        <button type="button" wire:click="openCreateMenuItem({{ $item->id }}, {{ $col }})"
                                            class="w-full py-1.5 rounded-lg bg-slate-50 hover:bg-amber-50 dark:bg-slate-800/50 dark:hover:bg-amber-950/40 text-slate-600 hover:text-amber-700 dark:text-slate-400 dark:hover:text-amber-300 text-[11px] font-semibold border border-dashed border-slate-200 dark:border-slate-700 hover:border-amber-400 transition flex items-center justify-center gap-1 cursor-pointer">
                                            <i class="fas fa-plus text-[9px]"></i>
                                            <span>{{ __('+ Add to Column') }} {{ $col }}</span>
                                        </button>
                                    </div>
                                </div>
                                @endfor
                            </div>
                        </div>
                        @elseif($item->children->isNotEmpty())
                        <!-- Standard Sub-Items (Indented Tree List) -->
                        <div class="ml-6 pl-4 border-l-2 border-dashed border-slate-300 dark:border-slate-700 space-y-2 pt-1">
                            @foreach($item->children as $childIndex => $child)
                            <div class="p-2.5 px-3.5 rounded-xl bg-white dark:bg-slate-900/90 border border-slate-200 dark:border-slate-800 flex items-center justify-between gap-3 hover:border-sky-300 dark:hover:border-sky-700 transition">
                                <div class="flex items-center gap-3 min-w-0">
                                    <!-- Hierarchy Indicator & Reorder -->
                                    <span class="text-slate-400 font-mono text-xs select-none">└─</span>
                                    <div class="flex flex-col gap-0.5 shrink-0 text-slate-400">
                                        <button type="button" wire:click="moveMenuItem({{ $child->id }}, 'up')"
                                            class="h-3.5 w-4 flex items-center justify-center hover:text-sky-600 cursor-pointer {{ $loop->first ? 'opacity-30 pointer-events-none' : '' }}" title="{{ __('Move Up') }}">
                                            <i class="fas fa-chevron-up text-[8px]"></i>
                                        </button>
                                        <button type="button" wire:click="moveMenuItem({{ $child->id }}, 'down')"
                                            class="h-3.5 w-4 flex items-center justify-center hover:text-sky-600 cursor-pointer {{ $loop->last ? 'opacity-30 pointer-events-none' : '' }}" title="{{ __('Move Down') }}">
                                            <i class="fas fa-chevron-down text-[8px]"></i>
                                        </button>
                                    </div>

                                    <!-- Sub-item Icon -->
                                    <div class="h-7 w-7 rounded-lg bg-sky-50 dark:bg-sky-950/50 border border-sky-100 dark:border-sky-800 flex items-center justify-center text-sky-600 dark:text-sky-400 shrink-0">
                                        <i class="{{ $child->icon ?: 'fas fa-angle-right' }} text-[11px]"></i>
                                    </div>

                                    <!-- Sub-item Title & Destination -->
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-1.5">
                                            <span class="text-xs font-bold text-slate-900 dark:text-white truncate">{{ $child->title }}</span>
                                            <span class="px-1.5 py-0.2 rounded-full text-[9px] font-mono uppercase {{ $child->type === 'page' ? 'bg-sky-100 text-sky-700 dark:bg-sky-950 dark:text-sky-300' : ($child->type === 'post' ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300') }}">
                                                {{ $child->type ?: 'custom' }}
                                            </span>
                                            @if($child->badge_text)
                                            <span class="inline-flex items-center px-1.5 py-0.2 rounded-full text-[9px] font-bold"
                                                style="background-color: {{ $child->badge_color ?? '#ef4444' }}22; color: {{ $child->badge_color ?? '#ef4444' }}; border: 1px solid {{ $child->badge_color ?? '#ef4444' }}44;">
                                                {{ $child->badge_text }}
                                            </span>
                                            @endif
                                        </div>
                                        @if($child->description)
                                        <div class="text-[11px] text-slate-500 dark:text-slate-400 truncate">
                                            {{ $child->description }}
                                        </div>
                                        @endif
                                        <div class="text-[10px] font-mono text-slate-400 truncate">
                                            {{ $child->resolved_url }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Sub-item Actions -->
                                <div class="flex items-center gap-1 shrink-0">
                                    <button wire:click="editMenuItem({{ $child->id }})"
                                        class="h-7 w-7 rounded-lg bg-slate-50 hover:bg-violet-50 dark:bg-slate-800 dark:hover:bg-violet-950/40 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 flex items-center justify-center transition cursor-pointer"
                                        title="{{ __('Edit Sub-link') }}">
                                        <i class="fas fa-edit text-[11px]"></i>
                                    </button>
                                    <button wire:click="deleteMenuItem({{ $child->id }})" wire:confirm="{{ __('Delete this sub-link?') }}"
                                        class="h-7 w-7 rounded-lg bg-slate-50 hover:bg-rose-50 dark:bg-slate-800 dark:hover:bg-rose-950/40 border border-slate-200 dark:border-slate-700 text-rose-600 dark:text-rose-400 flex items-center justify-center transition cursor-pointer"
                                        title="{{ __('Delete Sub-link') }}">
                                        <i class="fas fa-trash-alt text-[11px]"></i>
                                    </button>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    @empty
                    <div class="py-12 text-center text-slate-400 text-xs">
                        <i class="fas fa-link text-3xl mb-3 text-slate-300 dark:text-slate-700 block"></i>
                        {{ __('No navigation links in this menu yet. Click "+ Add Navigation Link" to start designing.') }}
                    </div>
                    @endforelse
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL 1: CREATE / EDIT NAVIGATION LINK                         -->
    <!-- ============================================================== -->
    @if($showMenuItemModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/70 backdrop-blur-xs overflow-y-auto">
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl w-full max-w-xl max-h-[90vh] flex flex-col overflow-hidden my-auto">
            <!-- Modal Header -->
            <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/50 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-2xl bg-violet-50 dark:bg-violet-950/50 border border-violet-200 dark:border-violet-800 flex items-center justify-center text-violet-600 dark:text-violet-400">
                        <i class="fas fa-sitemap"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900 dark:text-white">
                            {{ $editingMenuItemId ? __('Edit Menu Link') : ($item_parent_id ? __('Add Submenu Link') : __('Add Navigation Link')) }}
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">{{ __('Configure navigation hierarchy, mega menus, and link target.') }}</p>
                    </div>
                </div>
                <button wire:click="$set('showMenuItemModal', false)" class="text-slate-400 hover:text-slate-600 dark:hover:text-white transition cursor-pointer">
                    <i class="fas fa-times text-base"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 overflow-y-auto space-y-4 flex-1">
                @php
                    $selectedParent = $item_parent_id ? $rootMenuItems->firstWhere('id', (int)$item_parent_id) : null;
                    $isParentMega = $selectedParent && $selectedParent->is_mega_menu;
                @endphp

                <!-- Parent Menu Item (Hierarchy / Submenu) -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        {{ __('Menu Structure Level / Parent') }}
                    </label>
                    <select wire:model.live="item_parent_id"
                        class="w-full h-11 px-3.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500/50">
                        <option value="">{{ $selectedMenu && $selectedMenu->location === 'footer' ? __('★ Top-Level Column Header (e.g. Shop, Customer Support)') : __('★ Top-Level Item (Root Navigation Bar)') }}</option>
                        @foreach($rootMenuItems as $root)
                        @if(!$editingMenuItemId || $root->id !== (int)$editingMenuItemId)
                        <option value="{{ $root->id }}">↳ {{ $selectedMenu && $selectedMenu->location === 'footer' ? __('Link inside column: ') : __('Child under: ') }}{{ $root->title }} {{ $root->is_mega_menu ? ' (⚡ Mega Menu - ' . $root->mega_menu_columns . ' cols)' : '' }}</option>
                        @endif
                        @endforeach
                    </select>
                    @error('item_parent_id') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Column Selection (When Parent is a Mega Menu) -->
                @if($isParentMega)
                <div class="p-4 rounded-2xl bg-amber-50/80 dark:bg-amber-950/30 border border-amber-300/80 dark:border-amber-700/60 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold text-amber-900 dark:text-amber-200 flex items-center gap-1.5 uppercase tracking-wider">
                            <i class="fas fa-columns text-amber-500"></i> {{ __('Mega-menu column') }} <span class="text-rose-500">*</span>
                        </label>
                        <span class="text-[11px] font-semibold text-amber-700 dark:text-amber-300">
                            {{ $selectedParent->title }} ({{ $selectedParent->mega_menu_columns }} {{ __('cols') }})
                        </span>
                    </div>
                    <div class="grid grid-cols-{{ min((int)$selectedParent->mega_menu_columns, 4) }} gap-2">
                        @for($c = 1; $c <= min((int)$selectedParent->mega_menu_columns, 4); $c++)
                        <button type="button" wire:click="$set('item_mega_column', {{ $c }})"
                            class="py-2.5 rounded-xl text-xs font-bold border transition cursor-pointer flex flex-col items-center justify-center gap-0.5 {{ (int)$item_mega_column === $c ? 'bg-amber-500 text-white border-amber-600 shadow-xs ring-2 ring-amber-500/30' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:border-amber-400' }}">
                            <span class="text-[10px] uppercase font-mono opacity-80">{{ __('Col') }} {{ $c }}</span>
                            <span class="text-xs font-extrabold">{{ __('Column') }} {{ $c }}</span>
                        </button>
                        @endfor
                    </div>
                    <p class="text-[11px] text-amber-800/80 dark:text-amber-300/80">
                        {{ __('This link will be shown in') }} <strong>{{ __('Column') }} {{ $item_mega_column }}</strong> {{ __('of the mega menu layout.') }}
                    </p>
                </div>
                @elseif(!$item_parent_id)
                <!-- Mega Menu Settings for Root Items -->
                <div class="p-4 rounded-2xl bg-amber-50/60 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800/60 space-y-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model.live="item_is_mega_menu" class="rounded border-slate-300 text-amber-500 focus:ring-amber-400">
                        <span class="text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                            <i class="fas fa-th-large text-amber-500"></i> {{ __('Enable multi-column mega menu dropdown') }}
                        </span>
                    </label>

                    @if($item_is_mega_menu)
                    <div class="pt-2 border-t border-amber-200/60 dark:border-amber-800/40">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            {{ __('Number of columns') }}
                        </label>
                        <select wire:model="item_mega_menu_columns" class="w-full h-10 px-3 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200">
                            <option value="2">2 {{ __('Columns') }}</option>
                            <option value="3">3 {{ __('Columns') }}</option>
                            <option value="4">4 {{ __('Columns') }}</option>
                            <option value="5">5 {{ __('Columns') }}</option>
                            <option value="6">6 {{ __('Columns') }}</option>
                        </select>
                    </div>
                    @endif
                </div>
                @endif

                <!-- Navigation Label / Title -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        {{ __('Title / Navigation Label') }} <span class="text-rose-500">*</span>
                    </label>
                    <input wire:model="item_title" type="text" placeholder="e.g. Home, Shop, Deals, New Arrivals"
                        class="w-full h-11 px-3.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500/50">
                    @error('item_title') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Subtitle / Caption Description -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        {{ __('Subtitle / Caption Description') }} <span class="text-slate-400 font-normal lowercase">({{ __('optional, ideal for mega menu cards') }})</span>
                    </label>
                    <input wire:model="item_description" type="text" placeholder="e.g. Explore all handcrafted items and promotions"
                        class="w-full h-10 px-3.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 text-xs">
                    @error('item_description') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Content / Item Target Type (Buttons) -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        {{ __('Item Content Type') }}
                    </label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                        <button type="button" wire:click="$set('item_type', 'site')"
                            class="p-2.5 rounded-xl text-xs font-bold border text-center transition cursor-pointer {{ $item_type === 'site' ? 'bg-teal-50 dark:bg-teal-950 border-teal-400 text-teal-700 dark:text-teal-300' : 'bg-slate-50 dark:bg-slate-800/50 border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400' }}">
                            <i class="fas fa-store block mb-1"></i> {{ __('Site Page') }}
                        </button>
                        <button type="button" wire:click="$set('item_type', 'page')"
                            class="p-2.5 rounded-xl text-xs font-bold border text-center transition cursor-pointer {{ $item_type === 'page' ? 'bg-sky-50 dark:bg-sky-950 border-sky-400 text-sky-700 dark:text-sky-300' : 'bg-slate-50 dark:bg-slate-800/50 border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400' }}">
                            <i class="fas fa-file-alt block mb-1"></i> {{ __('CMS Page') }}
                        </button>
                        <button type="button" wire:click="$set('item_type', 'post')"
                            class="p-2.5 rounded-xl text-xs font-bold border text-center transition cursor-pointer {{ $item_type === 'post' ? 'bg-indigo-50 dark:bg-indigo-950 border-indigo-400 text-indigo-700 dark:text-indigo-300' : 'bg-slate-50 dark:bg-slate-800/50 border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400' }}">
                            <i class="fas fa-newspaper block mb-1"></i> {{ __('CMS Article') }}
                        </button>
                        <button type="button" wire:click="$set('item_type', 'category')"
                            class="p-2.5 rounded-xl text-xs font-bold border text-center transition cursor-pointer {{ $item_type === 'category' ? 'bg-emerald-50 dark:bg-emerald-950 border-emerald-400 text-emerald-700 dark:text-emerald-300' : 'bg-slate-50 dark:bg-slate-800/50 border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400' }}">
                            <i class="fas fa-tags block mb-1"></i> {{ __('Category') }}
                        </button>
                        <button type="button" wire:click="$set('item_type', 'custom')"
                            class="p-2.5 rounded-xl text-xs font-bold border text-center transition cursor-pointer {{ $item_type === 'custom' ? 'bg-violet-50 dark:bg-violet-950 border-violet-400 text-violet-700 dark:text-violet-300' : 'bg-slate-50 dark:bg-slate-800/50 border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400' }}">
                            <i class="fas fa-link block mb-1"></i> {{ __('Custom URL') }}
                        </button>
                        <button type="button" wire:click="$set('item_type', 'html')"
                            class="p-2.5 rounded-xl text-xs font-bold border text-center transition cursor-pointer {{ $item_type === 'html' ? 'bg-emerald-50 dark:bg-emerald-950 border-emerald-400 text-emerald-700 dark:text-emerald-300' : 'bg-slate-50 dark:bg-slate-800/50 border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400' }}">
                            <i class="fas fa-code block mb-1"></i> {{ __('Custom HTML Snippet') }}
                        </button>
                    </div>
                    <div class="grid grid-cols-1 gap-2 mt-2">
                        <button type="button" wire:click="$set('item_type', 'image')"
                            class="p-2.5 rounded-xl text-xs font-bold border text-center transition cursor-pointer {{ $item_type === 'image' ? 'bg-amber-50 dark:bg-amber-950 border-amber-400 text-amber-700 dark:text-amber-300' : 'bg-slate-50 dark:bg-slate-800/50 border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400' }}">
                            <i class="fas fa-image block mb-1"></i> {{ __('Image Menu Banner') }}
                        </button>
                    </div>
                </div>

                <!-- Conditional Selectors based on Type -->
                @if($item_type === 'site')
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        {{ __('Select Site Page') }} <span class="text-rose-500">*</span>
                    </label>
                    <select wire:model="item_site_path"
                        class="w-full h-11 px-3.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 text-sm">
                        <option value="">{{ __('Select a built-in page...') }}</option>
                        @foreach($sitePages as $path => $label)
                        <option value="{{ $path }}">{{ $label }} ({{ $path }})</option>
                        @endforeach
                    </select>
                    @error('item_site_path') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
                @elseif($item_type === 'page')
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        {{ __('Select CMS Page') }} <span class="text-rose-500">*</span>
                    </label>
                    <select wire:model="item_page_id"
                        class="w-full h-11 px-3.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 text-sm">
                        <option value="">{{ __('Select a custom page...') }}</option>
                        @foreach($allPages as $p)
                        <option value="{{ $p->id }}">{{ $p->title }} (/{{ $p->slug }})</option>
                        @endforeach
                    </select>
                    @error('item_page_id') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
                @elseif($item_type === 'post')
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        {{ __('Select Article / Post') }} <span class="text-rose-500">*</span>
                    </label>
                    <select wire:model="item_post_id"
                        class="w-full h-11 px-3.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 text-sm">
                        <option value="">{{ __('Select an article...') }}</option>
                        @foreach($allPosts as $p)
                        <option value="{{ $p->id }}">{{ $p->title }} (/post/{{ $p->slug }})</option>
                        @endforeach
                    </select>
                    @error('item_post_id') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
                @elseif($item_type === 'category')
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        {{ __('Select Store Category') }} <span class="text-rose-500">*</span>
                    </label>
                    <select wire:model="item_category_id"
                        class="w-full h-11 px-3.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 text-sm">
                        <option value="">{{ __('Select a store category...') }}</option>
                        @foreach($allCategories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }} (/category/{{ $cat->slug }})</option>
                        @endforeach
                    </select>
                    @error('item_category_id') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
                @elseif($item_type === 'custom')
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        {{ __('Custom URL Path') }} <span class="text-rose-500">*</span>
                    </label>
                    <input wire:model="item_url" type="text" placeholder="# or #section or /shop or https://example.com"
                        class="w-full h-11 px-3.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 text-sm font-mono">
                    <span class="text-[11px] text-slate-400 mt-1 block">
                        {{ __('Accepts # (placeholder/dropdown link), #section (page anchor), /path (internal route), or https://... (external URL).') }}
                    </span>
                    @error('item_url') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
                @elseif($item_type === 'image')
                <!-- Image Menu Card / Banner Settings -->
                <div class="p-4 rounded-2xl bg-amber-50/70 dark:bg-amber-950/20 border border-amber-300/80 dark:border-amber-700/60 space-y-3.5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-amber-900 dark:text-amber-200 flex items-center gap-1.5 uppercase tracking-wider">
                            <i class="fas fa-image text-amber-500"></i> {{ __('Image Menu Banner Card') }}
                        </span>
                        @if($item_image || $item_existing_image)
                        <button type="button" wire:click="removeMenuItemImage" class="text-xs font-bold text-rose-500 hover:text-rose-600 cursor-pointer">
                            {{ __('Remove Image') }}
                        </button>
                        @endif
                    </div>

                    <!-- Image Preview -->
                    @if($item_image)
                    <div class="relative rounded-xl overflow-hidden max-h-36 border border-slate-200 dark:border-slate-700">
                        <img src="{{ $item_image->temporaryUrl() }}" alt="" class="w-full h-32 object-cover">
                        <span class="absolute bottom-1 right-1 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-600 text-white">
                            {{ __('New Upload Preview') }}
                        </span>
                    </div>
                    @elseif(($pickedMediaUrls['item_image'][0] ?? null) !== null)
                    <div class="relative rounded-xl overflow-hidden max-h-36 border border-slate-200 dark:border-slate-700">
                        <img src="{{ $pickedMediaUrls['item_image'][0] }}" alt="" class="w-full h-32 object-cover">
                        <span class="absolute bottom-1 right-1 px-2 py-0.5 rounded text-[10px] font-bold bg-violet-600 text-white">
                            {{ __('Picked from Media') }}
                        </span>
                    </div>
                    @elseif($item_existing_image_url)
                    <div class="relative rounded-xl overflow-hidden max-h-36 border border-slate-200 dark:border-slate-700">
                        <img src="{{ $item_existing_image_url }}" alt="" class="w-full h-32 object-cover">
                        <span class="absolute bottom-1 right-1 px-2 py-0.5 rounded text-[10px] font-bold bg-slate-800 text-white">
                            {{ __('Current Image') }}
                        </span>
                    </div>
                    @endif

                    <!-- Upload Input -->
                    <div>
                        <input type="file" wire:model="item_image" accept="image/*"
                            class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-amber-500 file:text-white hover:file:bg-amber-600 cursor-pointer">
                        <p class="text-[10px] text-slate-400 mt-1">{{ __('PNG, JPG, WebP up to 3MB. Displayed prominently as a visual card inside the mega menu column.') }}</p>
                        @error('item_image') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        @if (auth()->user()?->can('media.view'))
                            <div class="mt-2 flex flex-wrap items-center gap-2">
                                <button type="button" wire:click="openMediaPicker('item_image')"
                                    class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer">
                                    {{ __('Choose from Media') }}
                                </button>
                                @if (($pickedMediaUrls['item_image'][0] ?? null) !== null)
                                    <button type="button" wire:click="clearPickedMedia('item_image')"
                                        class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer">
                                        {{ __('Clear Picked Image') }}
                                    </button>
                                @endif
                            </div>
                        @endif
                    </div>

                    <!-- Image Card Destination Link -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            {{ __('Click-Through URL / Destination Link') }} <span class="text-slate-400 font-normal">({{ __('optional') }})</span>
                        </label>
                        <input wire:model="item_url" type="text" placeholder="/shop/special or https://example.com"
                            class="w-full h-10 px-3.5 text-xs font-mono rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200">
                    </div>
                </div>
                @elseif($item_type === 'html')
                <!-- Custom HTML / Widget -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        {{ __('Custom HTML / Widget Content') }}
                    </label>
                    <textarea wire:model="item_content" rows="4" placeholder="<div class='p-3 bg-amber-50 rounded-xl'><h4>Sale 20% Off</h4><p class='text-xs'>Use promo code HUBAFRIKA</p></div>"
                        class="w-full p-3 text-xs font-mono rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-violet-500/50"></textarea>
                    <p class="text-[10px] text-slate-400 mt-1">{{ __('Render custom badges, contact snippets, or promo widgets directly into this mega menu column.') }}</p>
                    @error('item_content') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
                @endif

                <!-- Badges Configuration -->
                <div class="grid grid-cols-2 gap-3 items-center">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            {{ __('Badge Text') }} <span class="text-slate-400 font-normal lowercase">({{ __('optional') }})</span>
                        </label>
                        <input wire:model="item_badge_text" type="text" placeholder="e.g. New, Hot, Sale"
                            class="w-full h-10 px-3 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900 text-slate-800 dark:text-slate-200">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            {{ __('Badge Color') }}
                        </label>
                        <div class="flex items-center gap-2">
                            <input wire:model="item_badge_color" type="color"
                                class="h-10 w-12 rounded-xl border border-slate-200 dark:border-slate-700 bg-transparent p-1 cursor-pointer">
                            <span class="text-xs font-mono text-slate-500">{{ $item_badge_color }}</span>
                        </div>
                    </div>
                </div>

                <!-- Window Target & FontAwesome Icon -->
                <div class="grid grid-cols-2 gap-3 items-center">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            {{ __('Open In') }}
                        </label>
                        <select wire:model="item_target"
                            class="w-full h-10 px-3 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900 text-slate-800 dark:text-slate-200">
                            <option value="_self">{{ __('Same Window (_self)') }}</option>
                            <option value="_blank">{{ __('New Tab (_blank)') }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            {{ __('Optional Icon Class') }}
                        </label>
                        <input wire:model="item_icon" type="text" placeholder="fas fa-home or fas fa-shopping-bag"
                            class="w-full h-10 px-3 text-xs font-mono rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900 text-slate-800 dark:text-slate-200">
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="p-5 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 flex items-center justify-end gap-3 shrink-0">
                <button type="button" wire:click="$set('showMenuItemModal', false)"
                    class="px-5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-semibold text-xs hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer">
                    {{ __('Cancel') }}
                </button>
                <button type="button" wire:click="saveMenuItem"
                    class="px-6 py-2.5 rounded-xl bg-violet-600 hover:bg-violet-500 text-white font-bold text-xs shadow-md transition flex items-center gap-2 cursor-pointer">
                    <i class="fas fa-check text-xs"></i>
                    <span>{{ $editingMenuItemId ? __('Save Changes') : __('Add Link') }}</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- ============================================================== -->
    <!-- MODAL 2: CREATE / EDIT MENU CONTAINER                          -->
    <!-- ============================================================== -->
    @if($showMenuModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/70 backdrop-blur-xs">
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl w-full max-w-lg overflow-hidden my-auto">
            <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/50">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-2xl bg-violet-50 dark:bg-violet-950/50 border border-violet-200 dark:border-violet-800 flex items-center justify-center text-violet-600 dark:text-violet-400">
                        <i class="fas fa-compass"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900 dark:text-white">
                            {{ $editingMenuId ? __('Edit Menu') : __('Create Navigation Menu') }}
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">{{ __('Configure menu location and identifier.') }}</p>
                    </div>
                </div>
                <button wire:click="$set('showMenuModal', false)" class="text-slate-400 hover:text-slate-600 dark:hover:text-white transition cursor-pointer">
                    <i class="fas fa-times text-base"></i>
                </button>
            </div>

            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        {{ __('Menu Title') }} <span class="text-rose-500">*</span>
                    </label>
                    <input wire:model.live.debounce.300ms="menu_title" type="text" placeholder="e.g. Main Header Navigation"
                        class="w-full h-11 px-3.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500/50">
                    @error('menu_title') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            {{ __('Slug Identifier') }} <span class="text-rose-500">*</span>
                        </label>
                        <input wire:model="menu_slug" type="text" placeholder="header-menu"
                            class="w-full h-10 px-3 text-xs font-mono rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900 text-slate-800 dark:text-slate-200">
                        @error('menu_slug') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            {{ __('Display Location') }}
                        </label>
                        <select wire:model="menu_location"
                            class="w-full h-10 px-3 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900 text-slate-800 dark:text-slate-200">
                            <option value="header">{{ __('Header (Top Nav)') }}</option>
                            <option value="footer">{{ __('Footer Column') }}</option>
                            <option value="store">{{ __('Storefront Navigation') }}</option>
                            <option value="topbar">{{ __('Topbar Utility') }}</option>
                            <option value="sidebar">{{ __('Sidebar') }}</option>
                            <option value="mobile">{{ __('Mobile Menu') }}</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="cms-menu-description" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        {{ __('Description') }}
                    </label>
                    <x-rich-editor id="cms-menu-description" wire:model="menu_description" :compact="true" min-height="85px" placeholder="Optional notes on where this menu is placed..." />
                    @error('menu_description') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="p-5 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 flex items-center justify-end gap-3">
                <button type="button" wire:click="$set('showMenuModal', false)"
                    class="px-5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-semibold text-xs hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer">
                    {{ __('Cancel') }}
                </button>
                <button type="button" wire:click="saveMenu"
                    class="px-6 py-2.5 rounded-xl bg-violet-600 hover:bg-violet-500 text-white font-bold text-xs shadow-md transition flex items-center gap-2 cursor-pointer">
                    <i class="fas fa-check text-xs"></i>
                    <span>{{ $editingMenuId ? __('Save Changes') : __('Create Menu') }}</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- ============================================================== -->
    <!-- MODAL 3: MULTI-COLUMN MEGA MENU BUILDER                        -->
    <!-- ============================================================== -->
    @if($showMegaBuilderModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-md overflow-y-auto">
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl w-full max-w-7xl max-h-[92vh] flex flex-col overflow-hidden my-auto">
            <!-- Modal Header -->
            <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/50 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                        <i class="fas fa-th-large text-base"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-extrabold text-slate-900 dark:text-white">
                            {{ __('Edit Menu Item') }}
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">{{ __('Configure multi-column mega menu sections, links, and promo banners.') }}</p>
                    </div>
                </div>
                <button wire:click="$set('showMegaBuilderModal', false)" class="text-slate-400 hover:text-slate-600 dark:hover:text-white transition cursor-pointer">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 sm:p-8 overflow-y-auto space-y-6 flex-1 bg-slate-50/60 dark:bg-slate-950/40">
                <!-- Top Field: Mega Menu Header Title -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-2xs">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                        {{ __('Mega Menu Header Title') }}
                    </label>
                    <input wire:model="mega_header_title" type="text" placeholder="e.g. Featured Collections or Shop by Category"
                        class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 text-slate-800 dark:text-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500/50 font-medium">
                </div>

                <!-- Columns Section Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2">
                    <div class="flex items-center gap-2">
                        <h4 class="text-base font-extrabold text-slate-900 dark:text-white">
                            {{ __('Columns') }} ({{ count($mega_columns) }}/4)
                        </h4>
                        <span class="text-xs text-slate-400">({{ __('multi-column dropdown layout') }})</span>
                    </div>

                    <!-- + Add Column Actions (Max 4 Columns Enforced) -->
                    <div class="flex flex-wrap items-center gap-2">
                        @if(count($mega_columns) < 4)
                        <span class="text-xs font-bold text-slate-400 mr-1">+ {{ __('Add Column') }}:</span>
                        <!-- ≡ Link List (Indigo) -->
                        <button type="button" wire:click="addMegaColumn('links')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/50 dark:hover:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 font-bold text-xs shadow-2xs transition cursor-pointer">
                            <i class="fas fa-bars text-[10px]"></i>
                            <span>{{ __('Link List') }}</span>
                        </button>

                        <!-- 🖼 Thumbnail Cards (Green) -->
                        <button type="button" wire:click="addMegaColumn('thumbnails')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/50 dark:hover:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 font-bold text-xs shadow-2xs transition cursor-pointer">
                            <i class="fas fa-th-large text-[10px]"></i>
                            <span>{{ __('Thumbnail Cards') }}</span>
                        </button>

                        <!-- ≡ Promo Card (Orange) -->
                        <button type="button" wire:click="addMegaColumn('promo')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-50 hover:bg-amber-100 dark:bg-amber-950/50 dark:hover:bg-amber-900/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800 font-bold text-xs shadow-2xs transition cursor-pointer">
                            <i class="fas fa-bullhorn text-[10px]"></i>
                            <span>{{ __('Promo Card') }}</span>
                        </button>
                        @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                            <i class="fas fa-check-circle text-[11px] text-amber-500"></i> {{ __('Maximum 4 columns reached') }}
                        </span>
                        @endif
                    </div>
                </div>

                <!-- Columns Visual Cards Grid -->
                @if(empty($mega_columns))
                <div class="p-12 text-center rounded-3xl bg-white dark:bg-slate-900 border border-dashed border-slate-300 dark:border-slate-800">
                    <i class="fas fa-columns text-4xl text-slate-300 dark:text-slate-700 mb-3 block"></i>
                    <h5 class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ __('No columns added yet') }}</h5>
                    <p class="text-xs text-slate-400 mt-1 mb-4">{{ __('Click on "+ Add Column" buttons above to add Link Lists, Thumbnail Cards or Promo Cards.') }}</p>
                    <div class="flex items-center justify-center gap-2">
                        <button type="button" wire:click="addMegaColumn('links')" class="px-4 py-2 rounded-xl bg-indigo-600 text-white font-bold text-xs cursor-pointer">
                            <i class="fas fa-plus mr-1"></i> {{ __('Add Link List Column') }}
                        </button>
                    </div>
                </div>
                @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-{{ min(count($mega_columns), 4) }} gap-5 items-start">
                    @foreach($mega_columns as $colIndex => $col)
                    <div wire:key="mega-builder-col-{{ $colIndex }}-{{ $col['type'] ?? 'links' }}" class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col justify-between transition hover:border-slate-300 dark:hover:border-slate-700">
                        <div class="p-5 space-y-4">
                            <!-- Column Top Action Bar -->
                            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-black uppercase tracking-wider text-slate-400">
                                        #{{ $colIndex + 1 }}
                                    </span>
                                    <span class="text-xs font-black uppercase tracking-wider {{ ($col['type'] ?? 'links') === 'links' ? 'text-indigo-600 dark:text-indigo-400' : (($col['type'] ?? '') === 'thumbnails' ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400') }}">
                                        {{ ($col['type'] ?? 'links') === 'links' ? __('ROUTES / LINKS') : (($col['type'] ?? '') === 'thumbnails' ? __('THUMBNAIL CARDS') : __('PROMO CARD')) }}
                                    </span>
                                </div>

                                <!-- Move Left / Move Right / Delete Column -->
                                <div class="flex items-center gap-1">
                                    @if($colIndex > 0)
                                    <button type="button" wire:click="moveMegaColumn({{ $colIndex }}, 'left')"
                                        class="h-7 w-7 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 hover:text-slate-700 dark:hover:text-white flex items-center justify-center transition cursor-pointer" title="{{ __('Move Left') }}">
                                        <i class="fas fa-arrow-left text-[11px]"></i>
                                    </button>
                                    @endif

                                    @if($colIndex < count($mega_columns) - 1)
                                    <button type="button" wire:click="moveMegaColumn({{ $colIndex }}, 'right')"
                                        class="h-7 w-7 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 hover:text-slate-700 dark:hover:text-white flex items-center justify-center transition cursor-pointer" title="{{ __('Move Right') }}">
                                        <i class="fas fa-arrow-right text-[11px]"></i>
                                    </button>
                                    @endif

                                    <button type="button" wire:click="removeMegaColumn({{ $colIndex }})"
                                        class="h-7 w-7 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/40 text-rose-500 hover:text-rose-600 flex items-center justify-center transition cursor-pointer" title="{{ __('Delete Column') }}">
                                        <i class="fas fa-trash-alt text-[11px]"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Section Heading Input -->
                            <div>
                                <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1.5">
                                    {{ __('Section Heading') }}
                                </label>
                                <input wire:model="mega_columns.{{ $colIndex }}.heading" type="text" placeholder="e.g. Popular Categories or Best Sellers"
                                    class="w-full h-10 px-3.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 text-slate-800 dark:text-slate-200 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-amber-500/50">
                            </div>

                            <!-- TYPE 1: LINKS LIST -->
                            @if(($col['type'] ?? 'links') === 'links')
                            <div class="space-y-3 pt-1">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ __('Links List') }}</span>
                                    <button type="button" wire:click="addMegaColumnItem({{ $colIndex }})"
                                        class="text-xs font-bold text-amber-600 hover:text-amber-700 dark:text-amber-400 flex items-center gap-1 transition cursor-pointer">
                                        <i class="fas fa-plus text-[10px]"></i>
                                        <span>{{ __('+ Add Link') }}</span>
                                    </button>
                                </div>

                                <!-- Navigation Items in this column -->
                                <div class="space-y-3">
                                    @foreach($col['items'] ?? [] as $itemIdx => $navItem)
                                    <div class="p-3.5 rounded-2xl bg-slate-50/70 dark:bg-slate-800/40 border border-slate-200/80 dark:border-slate-700/80 space-y-2.5">
                                        <!-- Item Header -->
                                        <div class="flex items-center justify-between">
                                            <span class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 flex items-center gap-1.5">
                                                <span class="h-4 w-4 rounded-full bg-indigo-100 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 text-[10px] flex items-center justify-center font-bold">
                                                    {{ $itemIdx + 1 }}
                                                </span>
                                                {{ __('Navigation Item') }} #{{ $itemIdx + 1 }}
                                            </span>
                                            <button type="button" wire:click="removeMegaColumnItem({{ $colIndex }}, {{ $itemIdx }})"
                                                class="text-rose-400 hover:text-rose-600 transition cursor-pointer" title="{{ __('Delete Item') }}">
                                                <i class="fas fa-trash-alt text-[11px]"></i>
                                            </button>
                                        </div>

                                        <!-- Navigation Label -->
                                        <div>
                                            <label class="block text-[10px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400 mb-1 flex items-center gap-1">
                                                <i class="fas fa-bars text-[9px]"></i> {{ __('Navigation Label') }} *
                                            </label>
                                            <input wire:model="mega_columns.{{ $colIndex }}.items.{{ $itemIdx }}.label" type="text" placeholder="e.g. Electronics, Clothing..."
                                                class="w-full h-9 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 text-xs focus:outline-none focus:ring-1 focus:ring-amber-500">
                                        </div>

                                        <!-- Target URL & Badge Row -->
                                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-2">
                                            <div class="sm:col-span-8">
                                                <label class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 mb-1">
                                                    {{ __('Target URL') }}
                                                </label>
                                                <input wire:model="mega_columns.{{ $colIndex }}.items.{{ $itemIdx }}.url" type="text" placeholder="/shop or /category/..."
                                                    class="w-full h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 text-[11px] font-mono focus:outline-none focus:ring-1 focus:ring-amber-500">
                                            </div>
                                            <div class="sm:col-span-4">
                                                <label class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 mb-1">
                                                    {{ __('Badge') }}
                                                </label>
                                                <input wire:model="mega_columns.{{ $colIndex }}.items.{{ $itemIdx }}.badge" type="text" placeholder="New"
                                                    class="w-full h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 text-[11px] focus:outline-none focus:ring-1 focus:ring-amber-500">
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>

                                <!-- Bottom Button (Label & URL) -->
                                <div class="pt-2">
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                                        {{ __('Bottom Button (Label & URL)') }}
                                    </label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <input wire:model="mega_columns.{{ $colIndex }}.bottom_button_label" type="text" placeholder="View All"
                                            class="w-full h-9 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 text-slate-800 dark:text-slate-200 text-xs">
                                        <input wire:model="mega_columns.{{ $colIndex }}.bottom_button_url" type="text" placeholder="/shop"
                                            class="w-full h-9 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 text-slate-800 dark:text-slate-200 text-xs font-mono">
                                    </div>
                                </div>
                            </div>
                            @elseif(($col['type'] ?? '') === 'thumbnails')
                            <!-- TYPE 2: THUMBNAIL CARDS -->
                            <div class="space-y-3 pt-1">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ __('Thumbnail Cards') }}</span>
                                    <button type="button" wire:click="addMegaColumnItem({{ $colIndex }})"
                                        class="text-xs font-bold text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 flex items-center gap-1 transition cursor-pointer">
                                        <i class="fas fa-plus text-[10px]"></i>
                                        <span>{{ __('+ Add Card') }}</span>
                                    </button>
                                </div>

                                <div class="space-y-3">
                                    @foreach($col['items'] ?? [] as $itemIdx => $card)
                                    <div class="p-3.5 rounded-2xl bg-slate-50/70 dark:bg-slate-800/40 border border-slate-200/80 dark:border-slate-700/80 space-y-2.5">
                                        <div class="flex items-center justify-between">
                                            <span class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5">
                                                <span class="h-4 w-4 rounded-full bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 text-[10px] flex items-center justify-center font-bold">
                                                    {{ $itemIdx + 1 }}
                                                </span>
                                                {{ __('Card Item') }} #{{ $itemIdx + 1 }}
                                            </span>
                                            <button type="button" wire:click="removeMegaColumnItem({{ $colIndex }}, {{ $itemIdx }})"
                                                class="text-rose-400 hover:text-rose-600 transition cursor-pointer">
                                                <i class="fas fa-trash-alt text-[11px]"></i>
                                            </button>
                                        </div>

                                        <div>
                                            <label class="block text-[10px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 mb-1">
                                                {{ __('Card Title') }} *
                                            </label>
                                            <input wire:model="mega_columns.{{ $colIndex }}.items.{{ $itemIdx }}.title" type="text" placeholder="e.g. Handmade Crafts"
                                                class="w-full h-9 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 text-xs">
                                        </div>

                                        <div class="grid grid-cols-2 gap-2">
                                            <div>
                                                <label class="block text-[10px] font-semibold text-slate-500 mb-1">{{ __('Image URL / Path') }}</label>
                                                <input wire:model="mega_columns.{{ $colIndex }}.items.{{ $itemIdx }}.image" type="text" placeholder="storage/cms/... or https://"
                                                    class="w-full h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs font-mono">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-semibold text-slate-500 mb-1">{{ __('Subtitle / Caption') }}</label>
                                                <input wire:model="mega_columns.{{ $colIndex }}.items.{{ $itemIdx }}.subtitle" type="text" placeholder="Free Shipping Available"
                                                    class="w-full h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs">
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-2">
                                            <div class="sm:col-span-8">
                                                <label class="block text-[10px] font-semibold text-slate-500 mb-1">{{ __('Target URL') }}</label>
                                                <input wire:model="mega_columns.{{ $colIndex }}.items.{{ $itemIdx }}.url" type="text" placeholder="/shop/..."
                                                    class="w-full h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs font-mono">
                                            </div>
                                            <div class="sm:col-span-4">
                                                <label class="block text-[10px] font-semibold text-slate-500 mb-1">{{ __('Badge') }}</label>
                                                <input wire:model="mega_columns.{{ $colIndex }}.items.{{ $itemIdx }}.badge" type="text" placeholder="Popular"
                                                    class="w-full h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs">
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>

                                <div class="pt-2">
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                                        {{ __('Bottom Button (Label & URL)') }}
                                    </label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <input wire:model="mega_columns.{{ $colIndex }}.bottom_button_label" type="text" placeholder="Explore All"
                                            class="w-full h-9 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 text-xs">
                                        <input wire:model="mega_columns.{{ $colIndex }}.bottom_button_url" type="text" placeholder="/shop"
                                            class="w-full h-9 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 text-xs font-mono">
                                    </div>
                                </div>
                            </div>
                            @elseif(($col['type'] ?? '') === 'promo')
                            <!-- TYPE 3: PROMO BANNER CARD -->
                            <div class="space-y-3 pt-1">
                                <div>
                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400 mb-1">
                                        {{ __('Promo Banner Image URL') }}
                                    </label>
                                    <input wire:model="mega_columns.{{ $colIndex }}.image" type="text" placeholder="https://... or storage/cms/promo.jpg"
                                        class="w-full h-9 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs font-mono">
                                </div>

                                <div>
                                    <label for="mega-promo-content-{{ $colIndex }}" class="block text-[10px] font-semibold text-slate-600 dark:text-slate-400 mb-1">
                                        {{ __('Promo Body Content') }}
                                    </label>
                                    <x-rich-editor id="mega-promo-content-{{ $colIndex }}" wire:model="mega_columns.{{ $colIndex }}.content" :compact="true" min-height="95px" placeholder="Shop the new seasonal arrivals and get up to 20% discount on first orders..." />
                                </div>

                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-[10px] font-semibold text-slate-500 mb-1">{{ __('CTA Button Label') }}</label>
                                        <input wire:model="mega_columns.{{ $colIndex }}.button_label" type="text" placeholder="Shop Now"
                                            class="w-full h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-semibold text-slate-500 mb-1">{{ __('CTA Target URL') }}</label>
                                        <input wire:model="mega_columns.{{ $colIndex }}.button_url" type="text" placeholder="/shop"
                                            class="w-full h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs font-mono">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-semibold text-slate-500 mb-1">{{ __('Badge') }}</label>
                                    <input wire:model="mega_columns.{{ $colIndex }}.badge" type="text" placeholder="Special Offer"
                                        class="w-full h-8 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs">
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            <!-- Modal Footer -->
            <div class="p-5 border-t border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 flex items-center justify-between shrink-0">
                <span class="text-xs text-slate-400">
                    <i class="fas fa-info-circle mr-1 text-slate-400"></i>
                    {{ __('Columns are arranged horizontally in the mega menu dropdown.') }}
                </span>
                <div class="flex items-center gap-3">
                    <button type="button" wire:click="$set('showMegaBuilderModal', false)"
                        class="px-5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-semibold text-xs hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer">
                        {{ __('Cancel') }}
                    </button>
                    <button type="button" wire:click="saveMegaBuilder"
                        class="px-6 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow-md transition flex items-center gap-2 cursor-pointer">
                        <i class="fas fa-check text-xs"></i>
                        <span>{{ __('Save Multi-Column Layout') }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- ============================================================== -->
    <!-- MODAL 4: LIVE MEGA MENU PREVIEW                                -->
    <!-- ============================================================== -->
    @if($showMegaPreviewModal && $previewMegaMenu)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-md overflow-y-auto">
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl w-full max-w-5xl max-h-[90vh] flex flex-col overflow-hidden my-auto">
            <!-- Header -->
            <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/50 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                        <i class="fas fa-th-large text-base"></i>
                    </div>
                    <div>
                        <div class="inline-flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                            {{ __('Frontend Dropdown Preview') }} • {{ $previewMegaMenu->mega_menu_columns }} {{ __('Columns') }}
                        </div>
                        <h3 class="text-lg font-extrabold text-slate-900 dark:text-white mt-0.5">
                            {{ $previewMegaMenu->title }} — {{ __('Mega Menu Display') }}
                        </h3>
                    </div>
                </div>
                <button wire:click="$set('showMegaPreviewModal', false)" class="text-slate-400 hover:text-slate-600 dark:hover:text-white transition cursor-pointer">
                    <i class="fas fa-times text-base"></i>
                </button>
            </div>

            <!-- Body: The Simulated Dropdown Container -->
            <div class="p-6 sm:p-8 overflow-y-auto space-y-6 flex-1 bg-slate-100/60 dark:bg-slate-950/70">
                <div class="p-6 sm:p-8 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 shadow-xl space-y-6">
                    @php
                        $megaData = $previewMegaMenu->mega_menu_data ?: [];
                        $megaCols = $megaData['columns'] ?? [];
                        $headerTitle = $megaData['header_title'] ?? $previewMegaMenu->description;
                    @endphp

                    @if($headerTitle)
                    <div class="pb-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400 block mb-0.5">{{ __('Mega Menu Section') }}</span>
                            <h4 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">{{ $headerTitle }}</h4>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-xs font-mono font-bold bg-amber-500/10 text-amber-700 dark:text-amber-300 border border-amber-500/30">
                            {{ count($megaCols) ?: $previewMegaMenu->mega_menu_columns }} {{ __('Columns') }}
                        </span>
                    </div>
                    @endif

                    @if(!empty($megaCols))
                    <!-- Render from Mega Menu Multi-Column Builder Data -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-{{ min(count($megaCols), 4) }} gap-6 items-start">
                        @foreach($megaCols as $mCol)
                        <div class="space-y-4">
                            <div class="pb-2.5 border-b border-slate-100 dark:border-slate-800">
                                <h5 class="text-xs font-extrabold uppercase tracking-wider text-slate-900 dark:text-white flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full {{ ($mCol['type'] ?? '') === 'links' ? 'bg-indigo-500' : (($mCol['type'] ?? '') === 'thumbnails' ? 'bg-emerald-500' : 'bg-amber-500') }}"></span>
                                    <span>{{ $mCol['heading'] ?: __('Section') }}</span>
                                </h5>
                            </div>

                            @if(($mCol['type'] ?? '') === 'links')
                            <div class="space-y-2">
                                @forelse($mCol['items'] ?? [] as $mItem)
                                <a href="{{ $mItem['url'] ?: '#' }}" class="group flex items-center justify-between py-1.5 px-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 hover:text-amber-600 dark:hover:text-amber-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                                    <span class="truncate">{{ $mItem['label'] }}</span>
                                    @if(!empty($mItem['badge']))
                                    <span class="px-1.5 py-0.2 rounded-full text-[9px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300 shrink-0 ml-2">
                                        {{ $mItem['badge'] }}
                                    </span>
                                    @endif
                                </a>
                                @empty
                                <div class="text-xs text-slate-400 italic py-2">{{ __('No links added') }}</div>
                                @endforelse

                                @if(!empty($mCol['bottom_button_label']))
                                <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
                                    <a href="{{ $mCol['bottom_button_url'] ?: '#' }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-amber-600 hover:text-amber-700 dark:text-amber-400 transition">
                                        <span>{{ $mCol['bottom_button_label'] }}</span>
                                        <i class="fas fa-arrow-right text-[10px]"></i>
                                    </a>
                                </div>
                                @endif
                            </div>
                            @elseif(($mCol['type'] ?? '') === 'thumbnails')
                            <div class="space-y-3">
                                @forelse($mCol['items'] ?? [] as $mItem)
                                <a href="{{ $mItem['url'] ?: '#' }}" class="group block rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/40 hover:shadow-md transition">
                                    @if(!empty($mItem['image']))
                                    <div class="relative overflow-hidden aspect-video bg-slate-200 dark:bg-slate-700">
                                        <img src="{{ str_starts_with($mItem['image'], 'http') ? $mItem['image'] : asset($mItem['image']) }}" alt="" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                        @if(!empty($mItem['badge']))
                                        <span class="absolute top-2 left-2 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-500 text-white shadow-xs">
                                            {{ $mItem['badge'] }}
                                        </span>
                                        @endif
                                    </div>
                                    @endif
                                    <div class="p-3">
                                        <h6 class="text-xs font-bold text-slate-900 dark:text-white group-hover:text-amber-600 transition flex items-center justify-between">
                                            <span>{{ $mItem['title'] }}</span>
                                            <i class="fas fa-arrow-right text-[10px] opacity-0 group-hover:opacity-100 transition"></i>
                                        </h6>
                                        @if(!empty($mItem['subtitle']))
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 leading-snug">{{ $mItem['subtitle'] }}</p>
                                        @endif
                                    </div>
                                </a>
                                @empty
                                <div class="text-xs text-slate-400 italic py-2">{{ __('No cards added') }}</div>
                                @endforelse

                                @if(!empty($mCol['bottom_button_label']))
                                <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
                                    <a href="{{ $mCol['bottom_button_url'] ?: '#' }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 transition">
                                        <span>{{ $mCol['bottom_button_label'] }}</span>
                                        <i class="fas fa-arrow-right text-[10px]"></i>
                                    </a>
                                </div>
                                @endif
                            </div>
                            @elseif(($mCol['type'] ?? '') === 'promo')
                            <div class="p-4 rounded-2xl bg-gradient-to-br from-amber-500/10 via-amber-500/5 to-transparent border border-amber-500/20 space-y-3">
                                @if(!empty($mCol['badge']))
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-500 text-white shadow-xs inline-block">
                                    {{ $mCol['badge'] }}
                                </span>
                                @endif
                                @if(!empty($mCol['image']))
                                <div class="rounded-xl overflow-hidden aspect-video bg-slate-200 dark:bg-slate-700">
                                    <img src="{{ str_starts_with($mCol['image'], 'http') ? $mCol['image'] : asset($mCol['image']) }}" alt="" class="w-full h-full object-cover">
                                </div>
                                @endif
                                @if (\App\Support\RichText::plainText($mCol['content'] ?? '') !== '')
                                <div class="sf-rich-text sf-rich-text--compact text-xs text-slate-700 dark:text-slate-300 leading-relaxed">{!! \App\Support\RichText::toHtml($mCol['content']) !!}</div>
                                @endif
                                @if(!empty($mCol['button_label']))
                                <a href="{{ $mCol['button_url'] ?: '#' }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold transition">
                                    <span>{{ $mCol['button_label'] }}</span>
                                    <i class="fas fa-arrow-right text-[10px]"></i>
                                </a>
                                @endif
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @else
                    <!-- Fallback: Multi-Column layout from direct assigned children -->
                    <div class="grid grid-cols-1 md:grid-cols-{{ min((int)$previewMegaMenu->mega_menu_columns, 4) }} gap-6 items-start">
                        @for($c = 1; $c <= min((int)$previewMegaMenu->mega_menu_columns, 4); $c++)
                        @php
                            $cItems = $previewMegaMenu->children->filter(fn($child) => (int)($child->mega_column ?: ($child->mega_menu_column ?: 1)) === $c);
                        @endphp
                        <div class="space-y-3">
                            <div class="pb-2 border-b border-slate-100 dark:border-slate-800">
                                <h5 class="text-xs font-extrabold uppercase tracking-wider text-slate-900 dark:text-white flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    <span>{{ __('Column') }} {{ $c }}</span>
                                </h5>
                            </div>
                            <div class="space-y-2">
                                @forelse($cItems as $ch)
                                <a href="{{ $ch->resolved_url }}" class="flex items-center justify-between py-1 px-2 rounded-lg text-xs font-semibold text-slate-700 dark:text-slate-300 hover:text-amber-600 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                                    <span>{{ $ch->title }}</span>
                                    @if($ch->badge_text)
                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-bold" style="background-color: {{ $ch->badge_color ?? '#ef4444' }}22; color: {{ $ch->badge_color ?? '#ef4444' }};">
                                        {{ $ch->badge_text }}
                                    </span>
                                    @endif
                                </a>
                                @empty
                                <div class="text-xs text-slate-400 italic py-2">{{ __('No links in column') }}</div>
                                @endforelse
                            </div>
                        </div>
                        @endfor
                    </div>
                    @endif
                </div>
            </div>

            <!-- Footer -->
            <div class="p-5 border-t border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 flex items-center justify-between shrink-0">
                <span class="text-xs text-slate-400">
                    <i class="fas fa-check-circle text-emerald-500 mr-1"></i> {{ __('Simulated live preview of mega menu') }}
                </span>
                <button type="button" wire:click="$set('showMegaPreviewModal', false)"
                    class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs transition cursor-pointer">
                    {{ __('Close Preview') }}
                </button>
            </div>
        </div>
    </div>
    @endif

    @if ($mediaPickerOpen && auth()->user()?->can('media.view'))
        <livewire:admin.media-picker />
    @endif
</div>
