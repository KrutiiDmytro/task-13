@extends('layouts.app')

@section('title', $post->title)

@section('content')
    <div class="app-container page">

        @php
            $publishedAt = $post->published_at ?? $post->created_at;
            $cover = $post->image ? Storage::url($post->image) : null;
        @endphp

        <article class="article">
            <header class="article__header">
                @if($post->category)
                    <x-category-badge :category="$post->category" />
                @endif

                <h1 class="article__title">{{ $post->title }}</h1>

                <div class="article__meta">
                    @if($post->author_name)
                        <span>By {{ $post->author_name }}</span>
                        <span class="article__meta-dot"></span>
                    @endif
                    <time datetime="{{ $publishedAt?->toDateString() }}">
                        {{ $publishedAt?->format('M j, Y') }}
                    </time>
                </div>

                @if($cover)
                    <figure class="article__cover">
                        <img src="{{ $cover }}" alt="{{ $post->title }}" decoding="async">
                    </figure>
                @endif
            </header>

            <div class="prose">
                {!! nl2br(e($post->content)) !!}
            </div>

            <footer class="article__footer">
                @if($post->tags->count() > 0)
                    <span class="widget__title" style="margin: 0;">Tags</span>
                    <div class="tag-list">
                        @foreach($post->tags as $tag)
                            <a class="tag-pill" href="{{ route('public.tag', $tag->slug) }}">{{ $tag->name }}</a>
                        @endforeach
                    </div>
                @endif

                <a class="btn-ghost" style="margin-left: auto;" href="{{ route('home') }}">
                    <i class="fas fa-arrow-left" aria-hidden="true"></i> Back to all articles
                </a>
            </footer>
        </article>

        {{-- Comments --}}
        <section class="comments">
            <h2 class="comments__title">Comments ({{ $post->comments->count() }})</h2>

            @if($post->comments->count())
                @foreach($post->comments as $comment)
                    <article class="comment">
                        <div class="comment__avatar" aria-hidden="true">
                            {{ mb_strtoupper(mb_substr($comment->author_name ?? 'A', 0, 1)) }}
                        </div>
                        <div>
                            <span class="comment__author">{{ $comment->author_name ?? 'Anonymous' }}</span>
                            <span class="comment__date">· {{ $comment->created_at->format('M j, Y') }}</span>
                            <p class="comment__text">{{ $comment->content }}</p>
                        </div>
                    </article>
                @endforeach
            @else
                <p class="text-muted">No comments yet. Be the first to write one.</p>
            @endif

            <div class="comment-form">
                <h3 style="font-size: 1.1rem; margin-bottom: 18px;">Leave a comment</h3>

                <form action="{{ route('comments.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="post_id" value="{{ $post->id }}">

                    <div class="mb-3">
                        <label for="author" class="form-label">
                            Your name <span style="color: var(--cat-games);">*</span>
                        </label>
                        <input type="text"
                               class="form-control @error('author') is-invalid @enderror"
                               id="author"
                               name="author"
                               value="{{ old('author', auth()->user()->name ?? '') }}"
                               required>
                        @error('author')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="content" class="form-label">
                            Comment <span style="color: var(--cat-games);">*</span>
                        </label>
                        <textarea class="form-control @error('content') is-invalid @enderror"
                                  id="content"
                                  name="content"
                                  rows="4"
                                  required>{{ old('content') }}</textarea>
                        @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn-accent">
                        <i class="fas fa-paper-plane" aria-hidden="true"></i> Post comment
                    </button>
                </form>
            </div>
        </section>
    </div>
@endsection
