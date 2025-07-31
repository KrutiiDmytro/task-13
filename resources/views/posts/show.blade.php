@extends('layouts.app')

@section('title', $post->title)

@section('content')
<article>
    <h1>{{ $post->title }}</h1>
    <p class="text-muted">
        Опубликовано: {{ $post->date->format('d.m.Y') }}
        @if($post->category)
            | Категория: <a href="{{ route('posts.index', ['category_id' => $post->category->id]) }}">{{ $post->category->name }}</a>
        @endif
    </p>
    <div>
        @foreach($post->tags as $tag)
            <a href="{{ route('posts.index', ['tag_id' => $tag->id]) }}" class="badge bg-secondary text-decoration-none">{{ $tag->name }}</a>
        @endforeach
    </div>
    <hr>
    <div class="fs-5">
        {!! nl2br(e($post->content)) !!}
    </div>
</article>

<hr>

<section id="comments">
    <h3 class="mb-4">Комментарии ({{ $post->comments->count() }})</h3>
    @forelse($post->comments as $comment)
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">{{ $comment->author }}</h5>
                <p class="card-text">{{ $comment->content }}</p>
                <p class="card-text"><small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small></p>
            </div>
        </div>
    @empty
        <p>Комментариев пока нет. Будьте первым!</p>
    @endforelse
</section>
@endsection