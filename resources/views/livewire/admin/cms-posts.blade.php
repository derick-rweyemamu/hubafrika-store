<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Posts</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">Create and design storefront posts with the visual page builder.</p>
    </div>

    <x-flash-toast />

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(18rem,0.7fr)_minmax(0,2fr)]">
        <section class="h-fit space-y-4 rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
            <div>
                <h2 class="font-semibold text-gray-900 dark:text-gray-100">{{ $editingPostId ? 'Edit post details' : 'Create a post' }}</h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    @if ($editingPostId)
                        Update the post title, category, and publication status. The URL slug is regenerated from the title, and the existing design is preserved.
                    @else
                        Save the post details and status first. Open the saved post in the builder when you are ready to design it.
                    @endif
                </p>
            </div>

            <form wire:submit="savePost" class="space-y-4">
                <div>
                    <label for="cms-post-title" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Post title</label>
                    <input id="cms-post-title" type="text" wire:model.live="title" class="w-full rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-950">
                    @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="cms-post-slug" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">URL slug</label>
                    <input id="cms-post-slug" type="text" value="{{ $slug }}" placeholder="Generated from post title" readonly class="w-full rounded-lg border-gray-300 bg-gray-100 text-sm dark:border-gray-700 dark:bg-gray-800">
                    @error('slug') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="cms-post-category" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Category</label>
                    <select id="cms-post-category" wire:model="categoryId" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-950">
                        <option value="">Uncategorized</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('categoryId') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="cms-post-status" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Status</label>
                    <select id="cms-post-status" wire:model="status" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-950">
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                    </select>
                    @error('status') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <button type="submit" wire:loading.attr="disabled" class="admin-primary-bg rounded-lg px-4 py-2 text-sm font-semibold text-white disabled:opacity-60">
                    <span wire:loading.remove wire:target="savePost">{{ $editingPostId ? 'Save changes' : 'Save post' }}</span>
                    <span wire:loading wire:target="savePost">Saving...</span>
                </button>
                @if ($editingPostId)
                    <button type="button" wire:click="cancelPostEdit" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 dark:border-gray-700 dark:text-gray-200">Cancel</button>
                @endif
            </form>
        </section>

        <section class="h-fit space-y-3 rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">

            <section class="space-y-4 rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
        <div>
            <h2 class="font-semibold text-gray-900 dark:text-gray-100">Post categories</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400">Create categories to organize posts. Their URL slugs are generated from the name.</p>
        </div>
        <form wire:submit="saveCategory" class="grid grid-cols-1 items-end gap-3 sm:grid-cols-3">
            <div>
                <label for="cms-category-name" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Category name</label>
                <input id="cms-category-name" type="text" wire:model.live="categoryName" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-950">
                @error('categoryName') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="cms-category-slug" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">URL slug</label>
                <input id="cms-category-slug" type="text" value="{{ $categorySlug }}" readonly class="w-full rounded-lg border-gray-300 bg-gray-100 text-sm dark:border-gray-700 dark:bg-gray-800">
                @error('categorySlug') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="flex gap-2">
                <button type="submit" class="admin-primary-bg rounded-lg px-4 py-2 text-sm font-semibold text-white">{{ $editingCategoryId ? 'Save category' : 'Create category' }}</button>
                @if ($editingCategoryId)
                    <button type="button" wire:click="cancelCategoryEdit" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 dark:border-gray-700 dark:text-gray-200">Cancel</button>
                @endif
            </div>
        </form>
        @if ($categories->isNotEmpty())
            <ul class="flex flex-wrap gap-2">
                @foreach ($categories as $category)
                    <li wire:key="cms-category-{{ $category->id }}" class="inline-flex items-center gap-2 rounded-full bg-gray-100 px-3 py-1 text-xs text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                        <span>{{ $category->name }}</span>
                        <button type="button" wire:click="editCategory({{ $category->id }})" class="font-semibold text-sky-700 dark:text-sky-300">Edit</button>
                        <button type="button" wire:click="deleteCategory({{ $category->id }})" wire:confirm="Delete this category? Posts will become uncategorized." class="font-semibold text-red-600">Delete</button>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>



            <h2 class="font-semibold text-gray-900 dark:text-gray-100">All posts</h2>
            @forelse ($posts as $post)
                <article wire:key="cms-post-{{ $post->id }}" class="flex items-center justify-between gap-3 rounded-lg border border-gray-200 px-3 py-3 dark:border-gray-700">
                    <span class="min-w-0">
                        <span class="block truncate text-sm font-medium text-gray-900 dark:text-gray-100">{{ $post->title }}</span>
                        <span class="block truncate text-xs text-gray-500">/post/{{ $post->slug }}</span>
                        <select aria-label="Category for {{ $post->title }}" wire:change="assignCategory({{ $post->id }}, $event.target.value)" class="mt-1 rounded border-gray-300 py-1 text-xs dark:border-gray-700 dark:bg-gray-950">
                            <option value="">Uncategorized</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected((int) $post->category_id === $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </span>
                    <div class="flex shrink-0 items-center gap-2">
                        <span class="rounded-full px-2 py-1 text-xs {{ $post->isPublished() ? 'bg-green-100 text-green-800 dark:bg-green-950 dark:text-green-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                            {{ $post->isPublished() ? 'Published' : 'Draft' }}
                        </span>
                        <button type="button" wire:click="editPost({{ $post->id }})" class="rounded-lg border border-gray-300 px-3 py-2 text-xs font-semibold text-gray-700 dark:border-gray-700 dark:text-gray-200">Edit</button>
                        <a href="{{ route('admin.cms.posts.builder', $post) }}" class="admin-primary-bg rounded-lg px-3 py-2 text-xs font-semibold text-white">Open builder</a>
                    </div>
                </article>
            @empty
                <p class="text-sm text-gray-500 dark:text-gray-400">No posts yet.</p>
            @endforelse
        </section>

    </div>

    
</div>
