@extends('layouts.app')

@section('title', 'Просмотр комментария')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Просмотр комментария</h1>
        <div>
            <a href="{{ route('comments.edit', $comment) }}" class="btn btn-warning">Редактировать</a>
            <a href="{{ route('comments.index') }}" class="btn btn-secondary">Назад к списку</a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Комментарий #{{ $comment->id }}</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Автор:</strong> {{ $comment->author_name ?? 'Не указан' }}</p>
                    <p><strong>Email:</strong> {{ $comment->author_email ?? 'Не указан' }}</p>
                    <p><strong>Дата создания:</strong> {{ $comment->created_at->format('d.m.Y H:i') }}</p>
                    <p><strong>Дата обновления:</strong> {{ $comment->updated_at->format('d.m.Y H:i') }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Пост:</strong>
                        <a href="{{ route('posts.show', $comment->post) }}" class="text-decoration-none">
                            {{ $comment->post->title }}
                        </a>
                    </p>
                </div>
            </div>

            <hr>

            <div class="mt-3">
                <h6><strong>Содержание комментария:</strong></h6>
                <div class="bg-light p-3 rounded">
                    {{ $comment->content }}
                </div>
            </div>
        </div>
        <div class="card-footer">
            <form action="{{ route('comments.destroy', $comment) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('Вы уверены, что хотите удалить этот комментарий?')">
                    Удалить комментарий
                </button>
            </form>
        </div>
    </div>
@endsection
