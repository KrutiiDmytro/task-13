@props(['post', 'excerpt' => 120])

@php
    $url = route('public.post', $post->slug ?? $post->id);
    $date = $post->published_at ?? $post->created_at;
    $cover = $post->image ? Storage::url($post->image) : null;
@endphp

<article class="post-card">
    <div class="post-card__media">
        <a href="{{ $url }}" tabindex="-1" aria-hidden="true">
            @if($cover)
                <img src="{{ $cover }}" alt="" loading="lazy" decoding="async">
            @else
                {{-- No cover uploaded: fall back to a flat tinted panel --}}
                <span class="post-card__media-fallback" aria-hidden="true">
                    <i class="fas fa-gamepad"></i>
                </span>
            @endif
        </a>
    </div>

    <div class="post-card__body">
        @if($post->category)
            <x-category-badge :category="$post->category" />
        @endif

        <h3 class="post-card__title">
            <a href="{{ $url }}">{{ $post->title }}</a>
        </h3>

        <p class="post-card__excerpt">
            {{ \Illuminate\Support\Str::limit(strip_tags($post->content), $excerpt) }}
        </p>

        <div class="post-card__meta">
            <time datetime="{{ $date?->toDateString() }}">
                {{ $date?->format('M j, Y') }}
            </time>

            @if($post->tags && $post->tags->count())
                <span aria-hidden="true">·</span>
                <span>{{ $post->tags->count() }} {{ \Illuminate\Support\Str::plural('tag', $post->tags->count()) }}</span>
            @endif
        </div>
    </div>
</article>
