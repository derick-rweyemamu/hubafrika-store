@php
    $headerTitle = $pageHeader['title'] ?? '';
    $headerParents = is_array($pageHeader['parents'] ?? null) ? $pageHeader['parents'] : [];
    $headerTrail = $pageHeader['trail'] ?? $headerTitle;
@endphp

@if ($headerTitle !== '')
    <section class="hubafrika-page-header">
        <div class="hubafrika-page-header-inner">
            <h1 class="hubafrika-page-header-title">{{ $headerTitle }}</h1>
            <p class="hubafrika-page-header-crumb print:hidden">
                <a href="{{ url('/') }}" wire:navigate>Home</a>
                @foreach ($headerParents as $parent)
                    @if (! empty($parent['label']))
                        <span aria-hidden="true"> / </span>
                        @if (! empty($parent['url']))
                            <a href="{{ $parent['url'] }}" wire:navigate>{{ $parent['label'] }}</a>
                        @else
                            <span>{{ $parent['label'] }}</span>
                        @endif
                    @endif
                @endforeach
                <span aria-hidden="true"> / </span>
                <span>{{ $headerTrail }}</span>
            </p>
        </div>
    </section>
@endif

