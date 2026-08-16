@extends('layouts.app')

@section('title', 'Home')

@section('content')
    <div class="app-container page">

        @php
            // On the first page the newest post gets a wide "featured" treatment.
            $featured = $posts->currentPage() === 1 ? $posts->first() : null;
            $rest = $featured ? $posts->slice(1) : $posts;
        @endphp

        @if($featured)
            @php
                $featuredUrl = route('public.post', $featured->slug ?? $featured->id);
                $featuredDate = $featured->published_at ?? $featured->created_at;
                $featuredCover = $featured->image ? Storage::url($featured->image) : null;
            @endphp

            <section class="featured" aria-label="Featured article">
                <div class="featured__media">
                    <a href="{{ $featuredUrl }}" tabindex="-1" aria-hidden="true">
                        @if($featuredCover)
                            <img src="{{ $featuredCover }}" alt="" decoding="async">
                        @else
                            <span class="post-card__media-fallback" aria-hidden="true">
                                <i class="fas fa-gamepad"></i>
                            </span>
                        @endif
                    </a>
                </div>

                <div class="featured__body">
                    <span class="page-head__eyebrow">Featured</span>

                    @if($featured->category)
                        <x-category-badge :category="$featured->category" />
                    @endif

                    <h1 class="featured__title">
                        <a href="{{ $featuredUrl }}">{{ $featured->title }}</a>
                    </h1>

                    <p class="featured__excerpt">
                        {{ \Illuminate\Support\Str::limit(strip_tags($featured->content), 220) }}
                    </p>

                    <div class="post-card__meta" style="border: 0; padding-top: 0;">
                        <time datetime="{{ $featuredDate?->toDateString() }}">{{ $featuredDate?->format('M j, Y') }}</time>
                    </div>

                    <a class="btn-accent" href="{{ $featuredUrl }}">
                        Read article <i class="fas fa-arrow-right" aria-hidden="true"></i>
                    </a>
                </div>
            </section>
        @endif

        <div class="layout-with-sidebar">
            <div>
                <div class="page-head">
                    <h2 class="page-head__title">{{ $featured ? 'More stories' : 'Latest articles' }}</h2>
                </div>

                @if($rest->count() > 0)
                    <div class="post-grid">
                        @foreach($rest as $post)
                            <x-post-card :post="$post" />
                        @endforeach
                    </div>

                    <div class="d-flex justify-content-center mt-5">
                        {{ $posts->links() }}
                    </div>
                @elseif(! $featured)
                    <div class="empty-state">
                        <div class="empty-state__icon"><i class="fas fa-ghost" aria-hidden="true"></i></div>
                        <h2>Nothing published yet</h2>
                        <p>New guides, reviews and news are on the way.</p>
                    </div>
                @endif
            </div>

            @include('partials.sidebar')
        </div>
    </div>
@endsection
