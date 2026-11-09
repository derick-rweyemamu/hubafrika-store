<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Pages</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">Create and design storefront pages with the visual page builder.</p>
        </div>
    </div>

    <x-flash-toast />

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(18rem,0.7fr)_minmax(0,2fr)]">

        <section class="h-fit space-y-4 rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
            <div>
                <h2 class="font-semibold text-gray-900 dark:text-gray-100">{{ $editingPageId ? 'Edit page details' : 'Create a page' }}</h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    @if ($editingPageId)
                        Update the page title and publication status. The URL slug is regenerated from the title, and the existing design is preserved.
                    @else
                        Save the page details and status first. Open the saved page in the builder when you are ready to design it.
                    @endif
                </p>
            </div>

            <form wire:submit="savePage" class="space-y-4">
                <div>
                    <label for="cms-title" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Page title</label>
                    <input id="cms-title" type="text" wire:model.live="title" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-950">
                    @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="cms-slug" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">URL slug</label>
                    <input id="cms-slug" type="text" value="{{ $slug }}" placeholder="Generated from page title" readonly class="w-full rounded-lg border-gray-300 bg-gray-100 text-sm dark:border-gray-700 dark:bg-gray-800">
                    @error('slug') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="cms-status" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Status</label>
                    <select id="cms-status" wire:model="status" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-950">
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                    </select>
                    @error('status') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <button type="submit" wire:loading.attr="disabled" class="admin-primary-bg rounded-lg px-4 py-2 text-sm font-semibold text-white disabled:opacity-60">
                    <span wire:loading.remove wire:target="savePage">{{ $editingPageId ? 'Save changes' : 'Save page' }}</span>
                    <span wire:loading wire:target="savePage">Saving...</span>
                </button>
                @if ($editingPageId)
                    <button type="button" wire:click="startNewPage" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 dark:border-gray-700 dark:text-gray-200">Cancel</button>
                @endif
            </form>
        </section>


        <section class="h-fit space-y-3 rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <h2 class="font-semibold text-gray-900 dark:text-gray-100">All pages</h2>
            @forelse ($pages as $page)
                <article wire:key="cms-page-{{ $page->id }}" class="flex items-center justify-between gap-3 rounded-lg border border-gray-200 px-3 py-3 dark:border-gray-700">
                    <span class="min-w-0">
                        <span class="block truncate text-sm font-medium text-gray-900 dark:text-gray-100">{{ $page->title }}</span>
                        <a href="{{ route('pages.show', $page->slug) }}" target="_blank" rel="noopener" class="block truncate text-xs text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">/{{ $page->slug }}</a>
                    </span>
                    <div class="flex shrink-0 items-center gap-2">
                        <span class="rounded-full px-2 py-1 text-xs {{ $page->isPublished() ? 'bg-green-100 text-green-800 dark:bg-green-950 dark:text-green-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                            {{ $page->isPublished() ? 'Published' : 'Draft' }}
                        </span>
                        <button type="button" wire:click="editPage({{ $page->id }})" class="rounded-lg border border-gray-300 px-3 py-2 text-xs font-semibold text-gray-700 dark:border-gray-700 dark:text-gray-200">Edit</button>
                        <a href="{{ route('admin.cms.pages.builder', $page) }}" class="admin-primary-bg rounded-lg px-3 py-2 text-xs font-semibold text-white">Open builder</a>
                    </div>
                </article>
            @empty
                <p class="text-sm text-gray-500 dark:text-gray-400">No pages yet.</p>
            @endforelse
        </section>

        
    </div>
</div>
