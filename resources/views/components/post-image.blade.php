@props(['post', 'class' => 'img-fluid', 'style' => '', 'clickable' => true, 'showPlaceholder' => true])

@php
    $imageUrl = $post->image ? Storage::url($post->image) : 'https://via.placeholder.com/800x400?text=Нет+изображения';
    $hasRealImage = $post->image;
@endphp

@if($hasRealImage || $showPlaceholder)
    @if($clickable && $hasRealImage)
        <a href="{{ $imageUrl }}" target="_blank" rel="noopener">
            <img src="{{ $imageUrl }}"
                 alt="{{ $post->title }}"
                 class="{{ $class }}"
                 style="{{ $style }}">
        </a>
    @else
        <img src="{{ $imageUrl }}"
             alt="{{ $post->title }}"
             class="{{ $class }}"
             style="{{ $style }}">
    @endif
@endif
