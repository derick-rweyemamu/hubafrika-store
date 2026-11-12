@extends('components.layouts.app')

@section('title', $page->title . ' - ' . config('app.name', 'store'))

@section('content')
    @php
        $data = $page->builderData();
        $currency = \App\Support\Storefront\ProductGrid::currency();
        $html = !empty($data['html'])
            ? \App\Support\Storefront\PageBlocks::renderPageHeaders(
                \App\Support\Storefront\ProductGrid::render($data['html'], $currency),
                $page->title,
            )
            : '';
    @endphp

    @if(!empty($html))
        @if(!empty($data['css']))
            <style>
                {!! $data['css'] !!}
            </style>
        @endif

        {!! $html !!}

        @if(str_contains($html, 'data-hubafrika-'))
            {!! \App\Support\Storefront\PageBlocks::script('', url()->current()) !!}
        @endif

    @elseif(!empty($data['blocks']))
        <div>
            @foreach($data['blocks'] as $block)
                @if($block['type'] === 'text' || $block['type'] === 'heading')
                    <div>{!! \App\Support\RichText::toHtml($block['text'] ?? '') !!}</div>
                @elseif($block['type'] === 'image')
                    <img src="{{ $block['image_url'] ?? '' }}" alt="{{ $block['alt'] ?? '' }}">
                @elseif($block['type'] === 'button')
                    <a href="{{ $block['url'] ?? '#' }}">{{ $block['label'] ?? 'Button' }}</a>
                @elseif($block['type'] === 'product_grid')
                    @include('livewire.storefront.cms-products', ['products' => \App\Support\Storefront\ProductGrid::products(), 'currency' => $currency])
                @endif
            @endforeach
        </div>

    @else
        <div>
            <h1>{{ $page->title }}</h1>
            <p>This page has no content yet</p>
        </div>
    @endif
@endsection