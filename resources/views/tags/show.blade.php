@extends('layouts.app')

@section('title', $tag->name)

@section('content')
    <h1>Тег: {{ $tag->name }}</h1>
    
    <div class="mb-4">
        <a href="{{ route('tags.index') }}" class="btn btn-secondary">&larr; Назад к тегам</a>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Информация о теге</h5>
            <p><strong>ID:</strong> {{ $tag->id }}</p>
            <p><strong>Название:</strong> {{ $tag->name }}</p>
            <p><strong>Создан:</strong> {{ $tag->created_at->format('d.m.Y H:i') }}</p>
            <p><strong>Обновлен:</strong> {{ $tag->updated_at->format('d.m.Y H:i') }}</p>
        </div>
    </div>

    @if($tag->posts->count() > 0)
        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title">Посты с этим тегом ({{ $tag->posts->count() }})</h5>
                <div class="row">
                    @foreach($tag->posts as $post)
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <a href="{{ route('posts.show', $post) }}" class="text-decoration-none">
                                            {{ $post->title }}
                                        </a>
                                    </h6>
                                    <p class="card-text">{{ Str::limit($post->content, 100) }}</p>
                                    <small class="text-muted">{{ $post->date->format('d.m.Y') }}</small>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-info mt-4">
            Пока нет постов с этим тегом.
        </div>
    @endif
@endsection