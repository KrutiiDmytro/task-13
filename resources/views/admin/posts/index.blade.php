@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row">
        <!-- Основной контент -->
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Блог</h1>
                <a href="{{ route('posts.create') }}" class="btn btn-create">
                    <i class="fas fa-plus me-2"></i>Создать пост
                </a>
            </div>

            @if($posts->count() > 0)
                @foreach($posts as $post)
                    <div class="card post-card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">
                                <a href="{{ route('posts.show', $post) }}" class="text-decoration-none">
                                    {{ $post->title }}
                                </a>
                            </h5>
                            <p class="card-text">{{ \Illuminate\Support\Str::limit($post->content, 200) }}</p>
                            {{-- Теги поста (клікабельні для фільтрації) --}}
                            @if($post->tags->count() > 0)
                                <div class="mt-2 mb-2">
                                    @foreach($post->tags as $tag)
                                        <a href="{{ route('posts.index', array_merge(
                                            request()->only(['search', 'category']),
                                            ['tag' => $tag->id]
                                        )) }}" class="badge bg-secondary text-decoration-none me-1">
                                            #{{ $tag->name }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif

                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    {{ $post->date->format('d.m.Y') }} |
                                    Автор: {{ $post->user->name ?? $post->author_name ?? 'Аноним' }}
                                    @if($post->category)
                                        | <a href="{{ route('posts.index', ['category' => $post->category->id]) }}">{{ $post->category->name }}</a>
                                    @endif
                                </small>

                                <!-- Кнопки управления - только для авторизованных владельцев -->
                                @auth
                                    @if(auth()->id() === $post->user_id || auth()->user()->is_admin ?? false)
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('posts.edit', $post) }}" class="btn btn-edit btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('posts.destroy', $post) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-delete btn-sm"
                                                        onclick="return confirm('Вы уверены?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                @endauth
                            </div>
                        </div>
                        {{-- Изображение --}}
                        <x-post-image :post="$post"
                                    class="card-img-bottom"
                                    style="max-height:200px; object-fit:cover;"
                                    :showPlaceholder="false" />
                    </div>
                @endforeach



                <!-- Пагинация -->
                <div class="d-flex justify-content-center">
                    {{ $posts->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <h4 class="text-muted">Постов пока нет</h4>
                    <p class="text-muted">Будьте первым, кто создаст пост!</p>
                    <a href="{{ route('posts.create') }}" class="btn btn-create">
                        <i class="fas fa-plus me-2"></i>Создать первый пост
                    </a>
                </div>
            @endif
        </div>

        <!-- Боковая панель с фильтрами -->
        <div class="col-lg-4">
            <div class="sidebar-filter">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Фильтры</h5>
                        @if(request()->hasAny(['search','category','tag']))
                            <a href="{{ route('posts.index') }}" class="btn btn-sm btn-outline-secondary">Сбросить</a>
                        @endif
                    </div>
                    <div class="card-body">
                        <form action="{{ route('posts.index') }}" method="GET">

                            <!-- Поиск -->
                            <div class="mb-3">
                                <label class="form-label">Поиск по названию</label>
                                <input type="text" name="search" class="form-control"
                                       value="{{ request('search') }}" placeholder="Введите текст...">
                            </div>

                            <!-- Фильтр по категориям -->
                            @if($categories->count() > 0)
                                <div class="mb-3">
                                    <label class="form-label">Категории</label>
                                    @foreach($categories as $category)
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio"
                                                   name="category"
                                                   id="category_{{ $category->id }}"
                                                   value="{{ $category->id }}"
                                                   {{ request('category') == $category->id ? 'checked' : '' }}>
                                            <label class="form-check-label" for="category_{{ $category->id }}">
                                                {{ $category->name }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Фильтр по тегам -->
                            @if($tags->count() > 0)
                                <div class="mb-3">
                                    <label class="form-label">Теги</label>
                                    @foreach($tags as $tag)
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio"
                                                   name="tag"
                                                   id="tag_{{ $tag->id }}"
                                                   value="{{ $tag->id }}"
                                                   {{ request('tag') == $tag->id ? 'checked' : '' }}>
                                            <label class="form-check-label" for="tag_{{ $tag->id }}">
                                                {{ $tag->name }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Кнопка поиска -->
                            <button type="submit" class="btn btn-primary w-100">🔍 Найти</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
