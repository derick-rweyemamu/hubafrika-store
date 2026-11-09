<div class="space-y-6">

    {{-- Header: pick / create menu --}}
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Navigation menus</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Changes save instantly and appear on the site.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @if ($this->menus->isNotEmpty())
                <select wire:model.live="menuId" class="rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                    @foreach ($this->menus as $menu)
                        <option value="{{ $menu->id }}">{{ $menu->name }} ({{ $menu->slug }})</option>
                    @endforeach
                </select>
                <button wire:click="deleteMenu" wire:confirm="Delete this whole menu and all its items?"
                        class="rounded-lg px-3 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-gray-700">Delete menu</button>
            @endif

            <form wire:submit="createMenu" class="flex items-center gap-2">
                <input type="text" wire:model="newMenuName" placeholder="New menu name"
                       class="rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                <button class="rounded-lg border border-gray-300 px-3 py-2 text-sm hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">Create</button>
            </form>
        </div>
    </div>
    @error('newMenuName') <p class="text-xs text-red-600">{{ $message }}</p> @enderror

    @if ($menuId)
        @php($activeMenu = $this->menus->firstWhere('id', $menuId))

        {{-- Live preview --}}
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
            <p class="mb-3 text-xs font-medium uppercase tracking-wide text-gray-400">Live preview (hover items)</p>
            <div class="rounded-lg bg-white px-4 py-1 text-gray-800">
                <x-site-menu :slug="$activeMenu->slug" />
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-5">

            {{-- Items --}}
            <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800 lg:col-span-3">
                <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3 dark:border-gray-700">
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-200">Menu items</p>
                    <button wire:click="create" class="rounded-lg bg-green-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-green-700">+ Add top-level item</button>
                </div>

                <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse ($this->rows as $row)
                        @php($item = $row['item'])
                        <li wire:key="row-{{ $item->id }}" class="px-4 py-3" style="padding-left: {{ 1 + $row['depth'] * 1.5 }}rem">
                            <div class="flex flex-wrap items-center gap-3">
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-gray-900 dark:text-gray-100">{{ $item->label }}</p>
                                    <p class="truncate text-xs text-gray-400">
                                        @if ($item->page_id) Page: {{ $this->pages[$item->page_id] ?? '#'.$item->page_id }}
                                        @elseif ($item->post_id) Post: {{ $this->posts[$item->post_id] ?? '#'.$item->post_id }}
                                        @else {{ $item->url ?: '—' }}
                                        @endif
                                    </p>
                                </div>

                                <div class="flex items-center gap-1 text-xs text-gray-700 dark:text-gray-200">
                                    <button wire:click="move({{ $item->id }}, 'up')" class="rounded p-1 hover:bg-gray-100 dark:hover:bg-gray-700" title="Move up">↑</button>
                                    <button wire:click="move({{ $item->id }}, 'down')" class="rounded p-1 hover:bg-gray-100 dark:hover:bg-gray-700" title="Move down">↓</button>
                                    @if ($row['canAdd'])
                                        <button wire:click="create({{ $item->id }})" class="rounded px-2 py-1 text-green-700 hover:bg-green-50 dark:text-green-400 dark:hover:bg-gray-700">+ Sub</button>
                                    @endif
                                    <button wire:click="edit({{ $item->id }})" class="rounded px-2 py-1 hover:bg-gray-100 dark:hover:bg-gray-700">Edit</button>
                                    <button wire:click="delete({{ $item->id }})" wire:confirm="Delete this item and everything under it?"
                                            class="rounded px-2 py-1 text-red-600 hover:bg-red-50 dark:hover:bg-gray-700">Delete</button>
                                </div>
                            </div>

                            {{-- Top-level: mega menu controls --}}
                            @if ($row['depth'] === 0)
                                <div class="mt-2 flex flex-wrap items-center gap-3 text-xs text-gray-600 dark:text-gray-300">
                                    <label class="flex cursor-pointer items-center gap-2">
                                        <input type="checkbox" @checked($item->is_mega_menu) wire:click="toggleMega({{ $item->id }})"
                                               class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                                        Mega menu
                                    </label>

                                    @if ($item->is_mega_menu)
                                        <label class="flex items-center gap-1">Columns
                                            <select wire:change="setColumns({{ $item->id }}, $event.target.value)"
                                                    class="rounded-md border-gray-300 py-0.5 text-xs dark:border-gray-600 dark:bg-gray-700">
                                                @foreach (range(1, 6) as $n)
                                                    <option value="{{ $n }}" @selected($item->mega_menu_columns === $n)>{{ $n }}</option>
                                                @endforeach
                                            </select>
                                        </label>

                                        <label class="flex items-center gap-1">Layout
                                            <select wire:change="setMegaType({{ $item->id }}, $event.target.value)"
                                                    class="rounded-md border-gray-300 py-0.5 text-xs dark:border-gray-600 dark:bg-gray-700">
                                                @foreach (\App\Models\CmsMenuItem::MEGA_TYPES as $key => $name)
                                                    <option value="{{ $key }}" @selected($item->mega_menu_type === $key)>{{ $name }}</option>
                                                @endforeach
                                            </select>
                                        </label>
                                    @endif
                                </div>
                            @endif

                            {{-- Second level under a mega item: choose its column --}}
                            @if ($row['hasColumn'])
                                <div class="mt-2 text-xs text-gray-600 dark:text-gray-300">
                                    <label class="flex items-center gap-1">Column
                                        <select wire:change="setItemColumn({{ $item->id }}, $event.target.value)"
                                                class="rounded-md border-gray-300 py-0.5 text-xs dark:border-gray-600 dark:bg-gray-700">
                                            <option value="">Auto (1)</option>
                                            @foreach (range(1, $row['root']->mega_menu_columns) as $n)
                                                <option value="{{ $n }}" @selected($item->mega_menu_column === $n)>{{ $n }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                </div>
                            @endif
                        </li>
                    @empty
                        <li class="px-4 py-10 text-center text-sm text-gray-400">No items in this menu yet.</li>
                    @endforelse
                </ul>
            </div>

            {{-- Form --}}
            <div class="lg:col-span-2">
                @if ($showForm)
                    <form wire:submit="save" class="space-y-4 rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                        <h2 class="font-medium text-gray-900 dark:text-gray-100">
                            {{ $editingId ? 'Edit item' : ($parent_id ? 'New sub-item' : 'New top-level item') }}
                        </h2>

                        <div>
                            <label class="text-xs text-gray-500">Label</label>
                            <input type="text" wire:model="label" class="mt-1 w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                            @error('label') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-3 rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                            <p class="text-xs font-medium text-gray-500">Links to (choose one)</p>
                            <div>
                                <label class="text-xs text-gray-500">Page</label>
                                <select wire:model.live="page_id" class="mt-1 w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                    <option value="">—</option>
                                    @foreach ($this->pages as $id => $title) <option value="{{ $id }}">{{ $title }}</option> @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="text-xs text-gray-500">Post</label>
                                <select wire:model.live="post_id" class="mt-1 w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                    <option value="">—</option>
                                    @foreach ($this->posts as $id => $title) <option value="{{ $id }}">{{ $title }}</option> @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="text-xs text-gray-500">Custom URL (overrides page/post)</label>
                                <input type="text" wire:model="url" placeholder="/shop or https://…" class="mt-1 w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                            </div>
                        </div>

                        {{-- Column for a 2nd-level item inside a mega menu --}}
                        @if (count($this->formColumnChoices))
                            <div>
                                <label class="text-xs text-gray-500">Mega menu column</label>
                                <select wire:model="mega_menu_column" class="mt-1 w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                    <option value="">Auto (1)</option>
                                    @foreach ($this->formColumnChoices as $n) <option value="{{ $n }}">{{ $n }}</option> @endforeach
                                </select>
                            </div>
                        @endif

                        {{-- Mega menu settings: top-level items only --}}
                        @if (! $parent_id)
                            <div class="space-y-3 rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                                <label class="flex items-center gap-2 text-sm font-medium text-gray-800 dark:text-gray-200">
                                    <input type="checkbox" wire:model.live="is_mega_menu" class="rounded border-gray-300 text-green-600"> Enable mega menu
                                </label>

                                @if ($is_mega_menu)
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="text-xs text-gray-500">Columns</label>
                                            <select wire:model="mega_menu_columns" class="mt-1 w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                                @foreach (range(1, 6) as $n) <option value="{{ $n }}">{{ $n }}</option> @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="text-xs text-gray-500">Layout</label>
                                            <select wire:model.live="mega_menu_type" class="mt-1 w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                                @foreach (\App\Models\CmsMenuItem::MEGA_TYPES as $key => $name)
                                                    <option value="{{ $key }}">{{ $name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    @if ($mega_menu_type === 'image_menu')
                                        <div class="space-y-3">
                                            <div>
                                                <label class="text-xs text-gray-500">Image</label>
                                                <input type="file" wire:model="mega_menu_image_upload" accept="image/*" class="mt-1 block w-full text-xs">
                                                @error('mega_menu_image_upload') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror

                                                @if ($mega_menu_image_upload)
                                                    <img src="{{ $mega_menu_image_upload->temporaryUrl() }}" class="mt-2 h-24 rounded-lg object-cover">
                                                @elseif ($mega_menu_image)
                                                    <div class="mt-2 flex items-center gap-2">
                                                        <img src="{{ asset('storage/' . $mega_menu_image) }}" class="h-24 rounded-lg object-cover">
                                                        <button type="button" wire:click="removeImage" class="text-xs text-red-600">Remove</button>
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="grid grid-cols-2 gap-3">
                                                <div>
                                                    <label class="text-xs text-gray-500">Image position</label>
                                                    <select wire:model="mega_menu_image_position" class="mt-1 w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                                        <option value="left">Left</option>
                                                        <option value="right">Right</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="text-xs text-gray-500">Caption</label>
                                                    <input type="text" wire:model="mega_menu_image_title" class="mt-1 w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                                </div>
                                            </div>

                                            <div>
                                                <label class="text-xs text-gray-500">Image link (optional)</label>
                                                <input type="text" wire:model="mega_menu_image_link" placeholder="/shop/solar-panels" class="mt-1 w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                            </div>
                                        </div>
                                    @endif

                                    <p class="text-xs text-gray-400">Add sub-items as column headings, pick a column for each, then add links under them.</p>
                                @endif
                            </div>
                        @endif

                        <div class="flex justify-end gap-2">
                            <button type="button" wire:click="resetForm" class="rounded-lg px-3 py-2 text-sm text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">Cancel</button>
                            <button type="submit" class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">Save</button>
                        </div>
                    </form>
                @else
                    <div class="rounded-xl border border-dashed border-gray-300 p-8 text-center text-sm text-gray-400 dark:border-gray-700">
                        Choose <strong>Edit</strong> on an item, or add a new one.
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="rounded-xl border border-dashed border-gray-300 p-10 text-center text-sm text-gray-400 dark:border-gray-700">
            Create your first menu above to get started.
        </div>
    @endif
</div>
