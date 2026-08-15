@extends('layouts.app')

@section('title', 'Главная страница')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">

                @auth
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1>Последние посты</h1>
                        <div class="d-flex gap-2">
                            <span class="text-muted">Привет, {{ Auth::user()->name }}!</span>
                            <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary btn-sm">Профиль</a>
                            @if(Auth::user()->admin)
                                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-success btn-sm">Админ</a>
                            @endif
                            <a href="{{ route('posts.create') }}" class="btn btn-primary btn-sm">Создать пост</a>
                            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger btn-sm">Выйти</button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1>Последние посты</h1>
                        <div class="d-flex gap-2">
                            <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm">Войти</a>
                            <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Регистрация</a>
                        </div>
                    </div>
                @endauth

                @if($posts->count() > 0)
                    <div class="row">
                        @foreach($posts as $post)
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card h-100">

                                    {{-- изображение --}}
                                   <x-post-image :post="$post"
                                                class="card-img-top"
                                                style="height: 200px; object-fit: cover;"
                                                :clickable="false"
                                                :showPlaceholder="false" />

                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title">{{ $post->title }}</h5>
                                        <p class="card-text">{{ \Illuminate\Support\Str::limit($post->content, 100) }}</p>

                                        <div class="mt-auto">
                                            @if($post->category)
                                                <small class="text-muted">
                                                    Категория:
                                                    <a href="{{ route('public.category', $post->category->slug ?? $post->category->id) }}" class="text-decoration-none">
                                                        {{ $post->category->name }}
                                                    </a>
                                                </small>
                                                <br>
                                            @endif
                                            @if($post->tags && $post->tags->count() > 0)
                                                <small class="text-muted">
                                                    Теги:
                                                    @foreach($post->tags as $tag)
                                                        <a href="{{ route('public.tag', $tag->slug ?? $tag->id) }}" class="badge bg-secondary text-decoration-none me-1">
                                                            {{ $tag->name }}
                                                        </a>
                                                    @endforeach
                                                </small>
                                                <br>
                                            @endif
                                            <small class="text-muted">{{ $post->published_at?->format('d.m.Y') ?? $post->created_at->format('d.m.Y') }}</small>
                                        </div>

                                        <div class="mt-2">
                                            {{-- Используем slug если есть, иначе id --}}
                                            <a href="{{ route('public.post', $post->slug ?? $post->id) }}" class="btn btn-primary btn-sm">Читать далее</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="d-flex justify-content-center">
                        {{ $posts->links() }}
                    </div>
                @else
                    <div class="alert alert-info">
                        <h4>Пока нет опубликованных постов</h4>
                        <p>Скоро здесь появится интересный контент!</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
