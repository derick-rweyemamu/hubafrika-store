<div class="fixed inset-0 z-[60] overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="media-picker-title">
    <div class="fixed inset-0 bg-gray-950/60" wire:click="close"></div>

    <div class="relative flex min-h-full items-center justify-center p-4">
        <div class="w-full max-w-4xl overflow-hidden rounded-xl bg-white shadow-xl dark:bg-gray-900">
            <div class="flex items-start justify-between gap-3 border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                <div>
                    <h3 id="media-picker-title" class="text-lg font-semibold text-gray-900 dark:text-gray-100">Choose from media</h3>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Pick an existing picture. It is used where it already lives, so nothing is duplicated.</p>
                </div>
                <button type="button" wire:click="close" class="text-gray-400 transition hover:text-gray-600 dark:hover:text-gray-200" aria-label="Close media picker">
                    <i class="fas fa-times" aria-hidden="true"></i>
                </button>
            </div>

            <div class="space-y-3 border-b border-gray-200 px-5 py-3 dark:border-gray-800">
                <div class="flex flex-wrap gap-2">
                    <button type="button" wire:click="selectLibrary('all')"
                        class="rounded-full px-3 py-1.5 text-xs font-semibold transition {{ $library === 'all' ? 'admin-primary-bg text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700' }}">
                        All content
                    </button>
                    @foreach ($libraries as $key => $label)
                        <button type="button" wire:click="selectLibrary('{{ $key }}')"
                            class="rounded-full px-3 py-1.5 text-xs font-semibold transition {{ $library === $key ? 'admin-primary-bg text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>

                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search by name or folder..."
                    class="admin-primary-focus w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
            </div>

            <div class="max-h-[60vh] overflow-y-auto p-5">
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                    @forelse ($media as $file)
                        <button type="button" wire:key="media-picker-item-{{ $file->id }}" wire:click="select({{ $file->id }})"
                            @class([
                                'group relative overflow-hidden rounded-lg border bg-white text-left shadow-xs transition hover:border-[var(--admin-primary)] hover:shadow-md focus:outline-none focus:ring-2 focus:ring-[var(--admin-primary)] dark:bg-gray-950',
                                'border-[var(--admin-primary)] ring-2 ring-[var(--admin-primary)]' => in_array($file->id, $selectedIds, true),
                                'border-gray-200 dark:border-gray-700' => ! in_array($file->id, $selectedIds, true),
                            ])>
                            <span class="flex h-28 items-center justify-center bg-gray-50 dark:bg-gray-800">
                                <img src="{{ $file->getUrl() }}" alt="{{ $file->originalFileName() }}" loading="lazy" class="h-full w-full object-contain">
                            </span>
                            @if (in_array($file->id, $selectedIds, true))
                                <span class="absolute right-2 top-2 inline-flex h-5 w-5 items-center justify-center rounded-full bg-[var(--admin-primary)] text-[10px] font-bold text-white">
                                    <i class="fas fa-check" aria-hidden="true"></i>
                                </span>
                            @endif
                            <span class="block space-y-1 p-2">
                                <span class="block truncate text-xs font-semibold text-gray-900 dark:text-gray-100" title="{{ $file->originalFileName() }}">
                                    {{ $file->originalFileName() }}
                                </span>
                                <span class="flex flex-wrap items-center gap-1 text-[10px] text-gray-500 dark:text-gray-400">
                                    <span class="rounded-full bg-gray-100 px-1.5 py-0.5 font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-200">{{ $file->libraryLabel() }}</span>
                                    @if ($file->folder)
                                        <span class="truncate" title="{{ $file->folder }}">{{ $file->folder }}</span>
                                    @endif
                                </span>
                            </span>
                        </button>
                    @empty
                        <div class="col-span-full rounded-lg border border-dashed border-gray-300 px-6 py-10 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                            No images match this filter. Upload one on the Media screen first.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="flex flex-col gap-3 border-t border-gray-200 px-5 py-4 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                    <button type="button" wire:click="previousPage" @disabled($media->onFirstPage())
                        class="rounded-lg border border-gray-300 px-3 py-1.5 font-semibold text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800">
                        Previous
                    </button>
                    <span>Page {{ $media->currentPage() }} of {{ max(1, $media->lastPage()) }}</span>
                    <button type="button" wire:click="nextPage({{ $media->lastPage() }})" @disabled(! $media->hasMorePages())
                        class="rounded-lg border border-gray-300 px-3 py-1.5 font-semibold text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800">
                        Next
                    </button>
                </div>
                <button type="button" wire:click="close"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>
