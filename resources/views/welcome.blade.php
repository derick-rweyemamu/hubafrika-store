@extends('components.layouts.app')

@section('title', $page->title . ' - ' . config('app.name', 'store'))

@section('content')
    @php
        $data = $page->builderData();
    @endphp

    @if(!empty($data['html']))
        @if(!empty($data['css']))
            <style>
                {!! $data['css'] !!}
            </style>
        @endif

        {!! $data['html'] !!}

    @elseif(!empty($data['blocks']))
        <div>
            @foreach($data['blocks'] as $block)
                @if($block['type'] === 'text' || $block['type'] === 'heading')
                    <div>{!! $block['text'] ?? '' !!}</div>
                @elseif($block['type'] === 'image')
                    <img src="{{ $block['image_url'] ?? '' }}" alt="{{ $block['alt'] ?? '' }}">
                @elseif($block['type'] === 'button')
                    <a href="{{ $block['url'] ?? '#' }}">{{ $block['label'] ?? 'Button' }}</a>
                @elseif($block['type'] === 'product_grid')
                    <div>{!! $block['text'] ?? '' !!}</div>
                @endif
            @endforeach
        </div>

    @else
        <div>
            <h1>{{ $page->title }}</h1>
            <p>This page has no content yet.</p>
        </div>
    @endif
@endsection