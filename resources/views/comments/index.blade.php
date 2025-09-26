@extends('layouts.app')

@section('title', 'Все комментарии')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Все комментарии</h1>
        <a href="{{ route('comments.create') }}" class="btn btn-primary">Добавить комментарий</a>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Автор</th>
                        <th>Комментарий</th>
                        <th>Пост</th>
                        <th>Дата</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($comments as $comment)
                        <tr>
                            <td>{{ $comment->id }}</td>
                            <td>{{ $comment->author_name ?? 'Не указан' }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($comment->content, 50) }}</td>
                            <td>
                                <a href="{{ route('posts.show', $comment->post) }}" class="text-decoration-none">
                                    {{ \Illuminate\Support\Str::limit($comment->post->title, 30) }}
                                </a>
                            </td>
                            <td>{{ $comment->created_at->format('d.m.Y H:i') }}</td>
                            <td>
                                <a href="{{ route('comments.show', $comment) }}" class="btn btn-sm btn-info">Просмотр</a>
                                <a href="{{ route('comments.edit', $comment) }}" class="btn btn-sm btn-warning">Редактировать</a>
                                <form action="{{ route('comments.destroy', $comment) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Удалить комментарий?')">Удалить</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            
            <div class="d-flex justify-content-center">
                {{ $comments->links() }}
            </div>
        </div>
    </div>
@endsection