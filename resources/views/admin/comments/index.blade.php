@extends('adminlte::page')

@section('title', 'Управление комментариями')

@section('content_header')
    <h1>Управление комментариями</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Список комментариев</h3>
            <div class="card-tools">
                <a href="{{ route('admin.comments.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Создать комментарий
                </a>
            </div>
        </div>
        <div class="card-body">
            @if($comments->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Содержание</th>
                                <th>Пост</th>
                                <th>Автор</th>
                                <th>Email</th>
                                <th>Дата</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($comments as $comment)
                                <tr>
                                    <td>{{ $comment->id }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($comment->content, 50) }}</td>
                                    <td>
                                        @if($comment->post)
                                            <a href="{{ route('admin.posts.show', $comment->post) }}"
                                               class="text-primary">
                                                {{ \Illuminate\Support\Str::limit($comment->post->title, 30) }}
                                            </a>
                                        @else
                                            <span class="text-muted">Пост удален</span>
                                        @endif
                                    </td>
                                    <td>{{ $comment->author_name ?: 'Аноним' }}</td>
                                    <td>{{ $comment->author_email ?: 'Не указан' }}</td>
                                    <td>{{ $comment->created_at->format('d.m.Y H:i') }}</td>
                                    <td>
                                        <div class="btn-group" role="group" aria-label="Действия с комментарием #{{ $comment->id }}">
                                            <a href="{{ route('admin.comments.show', $comment) }}"
                                               class="btn btn-info btn-sm" title="Просмотр">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.comments.edit', $comment) }}"
                                               class="btn btn-warning btn-sm" title="Редактировать">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.comments.destroy', $comment) }}"
                                                  method="POST" class="d-inline"
                                                  onsubmit="return confirm('Вы уверены, что хотите удалить этот комментарий?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" title="Удалить">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Пагинация -->
                <div class="d-flex justify-content-center">
                    {{ $comments->links() }}
                </div>
            @else
                <div class="text-center">
                    <p>Комментариев пока нет.</p>
                    <a href="{{ route('admin.comments.create') }}" class="btn btn-primary">
                        Создать первый комментарий
                    </a>
                </div>
            @endif
        </div>
    </div>
@stop

@section('css')
    <style>
        .btn-group .btn {
            margin-right: 2px;
        }
        .table td {
            vertical-align: middle;
        }
    </style>
@stop

@section('js')
    <script>
        // Автообновление таблицы каждые 30 секунд
        setTimeout(() => {
            location.reload();
        }, 30000);
    </script>
@stop
