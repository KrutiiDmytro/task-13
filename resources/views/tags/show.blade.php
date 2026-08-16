@extends('layouts.app')

@section('title', $tag->name)

@section('content')
    <h1>Tag: {{ $tag->name }}</h1>

    <div class="mb-4">
        <a href="{{ route('tags.index') }}" class="btn btn-secondary">&larr; Back to tags</a>
        <a href="{{ route('tags.edit', $tag) }}" class="btn btn-warning">Edit</a>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Tag details</h5>
            <p><strong>ID:</strong> {{ $tag->id }}</p>
            <p><strong>Name:</strong> {{ $tag->name }}</p>
            <p><strong>Created:</strong> {{ $tag->created_at->format('d.m.Y H:i') }}</p>
            <p><strong>Updated:</strong> {{ $tag->updated_at->format('d.m.Y H:i') }}</p>
        </div>
    </div>

    {{--  Блок списку постів за тегом: підтримка пагінації ($posts) і fallback на $tag->posts --}}
@php
    //  Якщо контролер передав пагінований $posts — використовуємо його.
    // Інакше — підтягуємо пости напряму з моделі тегу.
    $isPaginated = isset($posts) && method_exists($posts, 'links');
    $list = $isPaginated ? $posts : $tag->posts()->with('tags')->latest('id')->get();
@endphp

@if($list->count() > 0)
    <div class="card mt-4">
        <div class="card-body">
            <h5 class="card-title">
                {{--  Рахуємо кількість з урахуванням пагінації (total) або колекції (count) --}}
                Posts with this tag ({{ $isPaginated ? $list->total() : $list->count() }})
            </h5>
            <div class="row">
                @foreach($list as $post)
                    <div class="col-md-6 mb-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <a href="{{ route('posts.show', $post) }}" class="text-decoration-none">
                                        {{ $post->title }}
                                    </a>
                                </h6>

                                {{--  Короткий анонс контенту --}}
                                <p class="card-text">
                                    {{ \Illuminate\Support\Str::limit(strip_tags($post->content ?? ''), 100) }}
                                </p>

                                {{--  Date, when the post has one --}}
                                @if(!empty($post->date))
                                    <small class="text-muted d-block mb-2">
                                        {{ $post->date->format('d.m.Y') }}
                                    </small>
                                @endif

                                {{--  Tags of this post, rendered as badges --}}
                                @if($post->relationLoaded('tags') ? $post->tags->isNotEmpty() : $post->tags()->exists())
                                    <div class="d-flex flex-wrap gap-2 mt-1">
                                        @foreach($post->tags as $t)
                                            <a href="{{ route('tags.show', $t->slug) }}" class="badge bg-secondary text-decoration-none">
                                                #{{ $t->name }}
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Пагінація, якщо $posts є LengthAwarePaginator --}}
            @if($isPaginated)
                <div class="mt-3">
                    {{ $list->links() }}
                </div>
            @endif
        </div>
    </div>
@else
    <div class="alert alert-info mt-4">
        No posts with this tag yet.
    </div>
@endif

@endsection
