<div>
    @if ($builderDocument !== null)
        <iframe
            id="cms-page-preview"
            title="{{ $page->title }}"
            sandbox="allow-scripts"
            srcdoc="{{ $builderDocument }}"
            class="w-full border-0"
            style="height: 85vh; min-height: 640px;"
        ></iframe>
        <script>
            window.addEventListener('message', function (event) {
                const preview = document.getElementById('cms-page-preview');

                if (
                    !preview
                    || event.source !== preview.contentWindow
                    || typeof event.data !== 'object'
                    || event.data === null
                    || !['hubafrika-cms-resize', 'hubafrika-cms-navigate'].includes(event.data.type)
                ) {
                    return;
                }

                if (event.data.type === 'hubafrika-cms-navigate' && typeof event.data.href === 'string') {
                    let destination;

                    try {
                        destination = new URL(event.data.href, window.location.href);
                    } catch {
                        return;
                    }

                    if (['http:', 'https:'].includes(destination.protocol)) {
                        window.location.assign(destination.href);
                    }

                    return;
                }

                const height = Number(event.data.height);

                if (Number.isFinite(height)) {
                    preview.style.height = `${Math.max(640, Math.min(height, 12000))}px`;
                }
            });
        </script>
    @else
        <article class="relative mx-auto max-w-7xl" style="min-height: {{ $pageHeight }}px">
            @foreach ($blocks as $index => $block)
                <section wire:key="public-page-block-{{ $index }}" class="absolute overflow-hidden" style="left: {{ $block['x'] ?? 0 }}%; top: {{ $block['y'] ?? 0 }}px; width: {{ $block['width'] ?? 100 }}%; min-height: {{ min((int) ($block['height'] ?? 140), 1500) }}px;">
                    @switch($block['type'])
                        @case('heading')
                            <h2 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-100 sm:text-4xl">{{ $block['text'] ?? '' }}</h2>
                            @break
                        @case('text')
                            <p class="whitespace-pre-line text-base leading-7 text-gray-700 dark:text-gray-300">{{ $block['text'] ?? '' }}</p>
                            @break
                        @case('image')
                            @if ($block['image_url'] !== '')
                                <img src="{{ $block['image_url'] }}" alt="{{ $block['alt'] ?? '' }}" loading="lazy" class="h-full w-full rounded-xl object-cover">
                            @endif
                            @break
                        @case('button')
                            <a href="{{ $block['url'] }}" class="inline-flex rounded-lg bg-gray-900 px-5 py-3 text-sm font-semibold text-white hover:bg-gray-700 dark:bg-white dark:text-gray-900">{{ $block['label'] }}</a>
                            @break
                        @case('product_grid')
                            <div class="space-y-4">
                                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $block['text'] }}</h2>
                                @include('livewire.storefront.cms-products', ['products' => $productBlocks, 'currency' => $currency])
                            </div>
                            @break
                    @endswitch
                </section>
            @endforeach
        </article>
    @endif
</div>
