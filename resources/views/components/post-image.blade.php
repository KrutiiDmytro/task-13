@props(['post', 'class' => 'img-fluid', 'style' => '', 'clickable' => true, 'showPlaceholder' => true])

@php
    // Inline SVG placeholder — the old via.placeholder.com service is gone,
    // so a self-contained data URI is used instead of a remote request.
    $placeholder = "data:image/svg+xml;charset=UTF-8,%3Csvg%20xmlns='http://www.w3.org/2000/svg'%20viewBox='0%200%20800%20450'%3E%3Crect%20width='800'%20height='450'%20fill='%231e212b'/%3E%3Ctext%20x='400'%20y='238'%20font-family='sans-serif'%20font-size='26'%20fill='%23697084'%20text-anchor='middle'%3ENo%20image%3C/text%3E%3C/svg%3E";

    $hasRealImage = (bool) $post->image;
    $imageUrl = $hasRealImage ? Storage::url($post->image) : $placeholder;
@endphp

@if($hasRealImage || $showPlaceholder)
    @if($clickable && $hasRealImage)
        <a href="{{ $imageUrl }}" target="_blank" rel="noopener">
            <img src="{{ $imageUrl }}"
                 alt="{{ $post->title }}"
                 class="{{ $class }}"
                 style="{{ $style }}"
                 loading="lazy"
                 decoding="async">
        </a>
    @else
        <img src="{{ $imageUrl }}"
             alt="{{ $hasRealImage ? $post->title : '' }}"
             class="{{ $class }}"
             style="{{ $style }}"
             loading="lazy"
             decoding="async">
    @endif
@endif
