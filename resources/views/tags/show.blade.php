@extends('layouts.app')

@section('title', $tag->name)

@section('content')
    <h1>Тег: {{ $tag->name }}</h1>
    
    <div class="mb-4">
        <a href="{{ route('tags.index') }}" class="btn btn-secondary">&larr; Назад к тегам</a>
        <a href="{{ route('tags.edit', $tag) }}" class="btn btn-warning">Редактировать</a>
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
                Посты с этим тегом ({{ $isPaginated ? $list->total() : $list->count() }})
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

                                {{--  Дата, якщо є атрибут date --}}
                                @if(!empty($post->date))
                                    <small class="text-muted d-block mb-2">
                                        {{ $post->date->format('d.m.Y') }}
                                    </small>
                                @endif

                                {{--  Теги цього поста як бейджі --}}
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
        Пока нет постов с этим тегом.
    </div>
@endif

@endsection