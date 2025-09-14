@extends('layouts.app')

@section('title', 'Просмотр комментария')

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>Просмотр комментария</h1>
                    <div>
                        <a href="{{ route('comments.index') }}" class="btn btn-secondary">Назад к списку</a>
                        @auth
                            <a href="{{ route('comments.edit', $comment) }}" class="btn btn-warning">Редактировать</a>
                            <form action="{{ route('comments.destroy', $comment) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Удалить комментарий?')">Удалить</button>
                            </form>
                        @endauth
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Комментарий #{{ $comment->id }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <strong>Автор:</strong>
                            </div>
                            <div class="col-sm-9">
                                {{ $comment->author }}
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <strong>Содержание:</strong>
                            </div>
                            <div class="col-sm-9">
                                <div class="border p-3 bg-light">
                                    {{ $comment->content }}
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <strong>Пост:</strong>
                            </div>
                            <div class="col-sm-9">
                                <a href="{{ route('posts.show', $comment->post) }}" class="text-decoration-none">
                                    {{ $comment->post->title }}
                                </a>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <strong>Дата создания:</strong>
                            </div>
                            <div class="col-sm-9">
                                {{ $comment->created_at->format('d.m.Y H:i:s') }}
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-3">
                                <strong>Дата обновления:</strong>
                            </div>
                            <div class="col-sm-9">
                                {{ $comment->updated_at->format('d.m.Y H:i:s') }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Дополнительная информация о посте -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Информация о посте</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <strong>Название поста:</strong>
                            </div>
                            <div class="col-sm-9">
                                <a href="{{ route('posts.show', $comment->post) }}" class="text-decoration-none">
                                    {{ $comment->post->title }}
                                </a>
                            </div>
                        </div>

                        @if($comment->post->category)
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <strong>Категория:</strong>
                            </div>
                            <div class="col-sm-9">
                                <span class="badge bg-primary">{{ $comment->post->category->name }}</span>
                            </div>
                        </div>
                        @endif

                        <div class="row">
                            <div class="col-sm-3">
                                <strong>Автор поста:</strong>
                            </div>
                            <div class="col-sm-9">
                                {{ $comment->post->user->name ?? $comment->post->author_name ?? 'Аноним' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection