@extends('layouts.app')

@section('title', $post->title)

@section('content')
    <div class="mb-4">
        <a href="{{ route('posts.index') }}" class="btn btn-secondary">&larr; Назад к постам</a>
        <a href="{{ route('posts.edit', $post) }}" class="btn btn-warning">Редактировать</a>
        <form action="{{ route('posts.destroy', $post) }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger" onclick="return confirm('Удалить пост?')">Удалить</button>
        </form>
    </div>

    <article class="card">
        @if($post->image)
            <img src="{{ Storage::url($post->image) }}" class="card-img-top" alt="{{ $post->title }}" style="max-height: 400px; object-fit: cover;">
        @endif
        
        <div class="card-body">
            <h1 class="card-title">{{ $post->title }}</h1>
            
            <div class="mb-3">
                <small class="text-muted">
                    {{ $post->created_at->format('d.m.Y H:i') }}
                    @if($post->category)
                        | Категория: <a href="{{ route('posts.index', ['category_id' => $post->category->id]) }}">{{ $post->category->name }}</a>
                    @endif
                </small>
            </div>
            
            <div class="mb-3">
                @foreach($post->tags as $tag)
                    <a href="{{ route('posts.index', ['tag_id' => $tag->id]) }}" class="badge bg-secondary text-decoration-none">{{ $tag->name }}</a>
                @endforeach
            </div>
            
            <div class="card-text">
                {!! nl2br(e($post->content)) !!}
            </div>
        </div>
    </article>

    <!-- Комментарии -->
    <div class="mt-5">
        <h3>Комментарии ({{ $post->comments->count() }})</h3>
        
        @if($post->comments->count() > 0)
            @foreach($post->comments as $comment)
                <div class="card mb-3">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2 text-muted">{{ $comment->author }}</h6>
                        <p class="card-text">{{ $comment->content }}</p>
                        <small class="text-muted">{{ $comment->created_at->format('d.m.Y H:i') }}</small>
                    </div>
                </div>
            @endforeach
        @else
            <p class="text-muted">Пока нет комментариев.</p>
        @endif
    </div>
@endsection