@extends('layouts.app')

@section('title', 'Все посты')

@section('content')
<div class="row">
    <!-- Основной контент (посты) -->
    <div class="col-md-8">
        <h1 class="mb-4">Все посты</h1>
        @forelse($posts as $post)
            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="card-title h4">
                        <a href="{{ route('posts.show', $post) }}" class="text-decoration-none">{{ $post->title }}</a>
                    </h2>
                    <p class="text-muted small">
                        {{ $post->date->format('d.m.Y') }} |
                        @if($post->category)
                            <a href="{{ route('posts.index', ['category_id' => $post->category->id]) }}">{{ $post->category->name }}</a>
                        @endif
                    </p>
                    <p class="card-text">{{ Str::limit($post->content, 150) }}</p>
                    <div>
                        @foreach($post->tags as $tag)
                            <a href="{{ route('posts.index', ['tag_id' => $tag->id]) }}" class="badge bg-secondary text-decoration-none">{{ $tag->name }}</a>
                        @endforeach
                    </div>
                </div>
            </div>
        @empty
            <p>Пока нет ни одного поста.</p>
        @endforelse

        <!-- Пагинация -->
        <div class="d-flex justify-content-center">
            {{ $posts->links() }}
        </div>
    </div>

    <!-- Боковая панель (фильтры) -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Фильтры</h5>
                <form action="{{ route('posts.index') }}" method="GET">
                    <div class="mb-3">
                        <label for="search_title" class="form-label">Поиск по названию</label>
                        <input type="text" id="search_title" name="search_title" class="form-control" value="{{ request('search_title') }}">
                    </div>
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Категория</label>
                        <select id="category_id" name="category_id" class="form-select">
                            <option value="">Все категории</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="tag_id" class="form-label">Тег</label>
                        <select id="tag_id" name="tag_id" class="form-select">
                            <option value="">Все теги</option>
                            @foreach($tags as $tag)
                                <option value="{{ $tag->id }}" @selected(request('tag_id') == $tag->id)>
                                    {{ $tag->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Применить</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection