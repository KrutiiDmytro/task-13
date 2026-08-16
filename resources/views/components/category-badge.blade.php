@props(['category', 'linked' => true])

@php
    // Per-category accent colour, keyed on the slug (news / tips / tutorials / games).
    $slug = $category->slug ?? \Illuminate\Support\Str::slug($category->name ?? '');
    $known = ['news', 'tips', 'tutorials', 'games'];
    $modifier = in_array($slug, $known, true) ? " cat-badge--{$slug}" : '';
@endphp

@if($linked && $slug)
    <a class="cat-badge{{ $modifier }}" href="{{ route('public.category', $slug) }}">
        {{ $category->name }}
    </a>
@else
    <span class="cat-badge{{ $modifier }}">{{ $category->name }}</span>
@endif
