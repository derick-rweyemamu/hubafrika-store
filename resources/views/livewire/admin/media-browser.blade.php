@php
    $canManage = auth()->user()?->can('media.manage') ?? false;
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Media library</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Every image uploaded to the shop. Files keep their original name and are grouped per folder.
            </p>
        </div>
        @if ($canManage)
            <button type="button" wire:click="create" class="admin-primary-bg rounded-lg px-4 py-2.5 text-sm font-semibold text-white">Upload file</button>
        @endif
    </div>

    <x-flash-toast />

    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex flex-wrap gap-2">
            <button type="button" wire:click="selectLibrary('all')"
                class="rounded-full px-3.5 py-1.5 text-xs font-semibold transition {{ $library === 'all' ? 'admin-primary-bg text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700' }}">
                All content
            </button>
            @foreach ($libraries as $key => $label)
                <button type="button" wire:click="selectLibrary('{{ $key }}')"
                    class="rounded-full px-3.5 py-1.5 text-xs font-semibold transition {{ $library === $key ? 'admin-primary-bg text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <div class="flex items-center gap-3">
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search by name or folder..."
                class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-900 lg:w-64">
            <select wire:model.live="perPage" class="admin-primary-focus rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-900">
                <option value="12">12</option>
                <option value="24">24</option>
                <option value="48">48</option>
            </select>
        </div>
    </div>

    @if ($library === 'users')
        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-800 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-200">
            Account pictures are kept in a separate folder and never listed together with products, pages or posts.
        </div>
    @endif

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @forelse ($media as $file)
            <div wire:key="media-{{ $file->id }}" class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex h-40 items-center justify-center bg-gray-50 dark:bg-gray-800">
                    <img src="{{ $file->getUrl() }}" alt="{{ $file->file_name }}" loading="lazy" class="h-full w-full object-contain">
                </div>
                <div class="space-y-2 p-4">
                    <div class="truncate text-sm font-semibold text-gray-900 dark:text-gray-100" title="{{ $file->originalFileName() }}">
                        {{ $file->originalFileName() }}
                    </div>
                    <div class="flex flex-wrap items-center gap-1.5 text-[11px] text-gray-500 dark:text-gray-400">
                        <span class="rounded-full bg-gray-100 px-2 py-0.5 font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-200">{{ $file->libraryLabel() }}</span>
                        @if ($file->folder)
                            <span class="truncate rounded-full bg-gray-100 px-2 py-0.5 dark:bg-gray-800" title="{{ $file->folder }}">{{ $file->folder }}</span>
                        @endif
                        <span>{{ $file->human_readable_size }}</span>
                    </div>
                    <div class="text-[11px] text-gray-400 dark:text-gray-500">
                        {{ $file->created_at?->format('d M Y H:i') }}
                        @if ($file->uploader)
                            &middot; {{ $file->uploader->name }}
                        @endif
                    </div>
                    <div class="flex items-center justify-between pt-1">
                        <a href="{{ $file->getUrl() }}" target="_blank" rel="noopener" class="text-xs font-semibold admin-primary-text hover:underline">Open</a>
                        @if ($canManage)
                            <button type="button" wire:click="delete({{ $file->id }})" wire:confirm="Delete this file? Anything still pointing at it will show a broken image."
                                class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-red-50 text-red-600 transition hover:bg-red-100 dark:bg-red-950 dark:text-red-300 dark:hover:bg-red-900"
                                title="Delete" aria-label="Delete file">
                                <i class="fas fa-trash-can text-xs" aria-hidden="true"></i>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-xl border border-dashed border-gray-300 bg-white px-6 py-12 text-center text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400">
                No files in this folder yet.
            </div>
        @endforelse
    </div>

    <div>
        {{ $media->links() }}
    </div>

    @if ($canManage && $isOpen)
        <div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/50 p-4 sm:p-8">
            <div class="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl dark:bg-gray-900">
                <div class="flex items-start justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Upload file</h3>
                    <button type="button" wire:click="$set('isOpen', false)" class="text-gray-400 transition hover:text-gray-600 dark:hover:text-gray-200" aria-label="Close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form wire:submit="save" class="mt-5 space-y-4">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400" for="media-upload">File</label>
                        <input id="media-upload" type="file" wire:model="upload" accept="image/*"
                            class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-sm file:font-semibold dark:text-gray-300 dark:file:bg-gray-800">
                        @error('upload') <span class="mt-1 block text-xs text-rose-500">{{ $message }}</span> @enderror
                        <div wire:loading wire:target="upload" class="mt-2 text-xs text-gray-500 dark:text-gray-400">Uploading...</div>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400" for="media-library">Folder</label>
                        <select id="media-library" wire:model="uploadLibrary"
                            class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                            @foreach ($libraries as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('uploadLibrary') <span class="mt-1 block text-xs text-rose-500">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400" for="media-folder">Sub folder <span class="font-normal normal-case">(optional)</span></label>
                        <input id="media-folder" type="text" wire:model="uploadFolder" placeholder="banners/2026"
                            class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                        @error('uploadFolder') <span class="mt-1 block text-xs text-rose-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('isOpen', false)" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 dark:border-gray-700 dark:text-gray-200">Cancel</button>
                        <button type="submit" class="admin-primary-bg rounded-lg px-4 py-2 text-sm font-semibold text-white" wire:loading.attr="disabled">
                            Save file
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
