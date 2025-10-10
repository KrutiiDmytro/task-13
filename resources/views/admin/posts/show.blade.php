@extends('adminlte::page')

@section('title', 'Просмотр поста')

@section('content_header')
    <h1>Просмотр поста: {{ $post->title }}</h1>
@stop

@section('content')
    <div class="row">
        <!-- Основная информация о посте -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Информация о посте</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.posts.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Назад к списку
                        </a>
                        <a href="{{ route('admin.posts.edit', $post) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Редактировать
                        </a>
                        <a href="{{ route('posts.show', $post) }}" class="btn btn-info btn-sm" target="_blank">
                            <i class="fas fa-external-link-alt"></i> Просмотр на сайте
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Заголовок -->
                    <div class="form-group">
                        <label><strong>Заголовок:</strong></label>
                        <h3>{{ $post->title }}</h3>
                    </div>

                    <!-- Метаинформация -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <strong>ID:</strong> {{ $post->id }}
                        </div>
                        <div class="col-md-3">
                            <strong>Автор:</strong> {{ $post->user->name ?? 'Неизвестно' }}
                        </div>
                        <div class="col-md-3">
                            <strong>Дата:</strong> {{ $post->date ? $post->date->format('d.m.Y') : 'Не указана' }}
                        </div>
                        <div class="col-md-3">
                            <strong>Создан:</strong> {{ $post->created_at->format('d.m.Y H:i') }}
                        </div>
                    </div>

                    <!-- Категория -->
                    <div class="form-group">
                        <label><strong>Категория:</strong></label>
                        @if($post->category)
                            <span class="badge badge-info badge-lg">{{ $post->category->name }}</span>
                        @else
                            <span class="badge badge-secondary badge-lg">Без категории</span>
                        @endif
                    </div>

                    <!-- Теги -->
                    <div class="form-group">
                        <label><strong>Теги:</strong></label>
                        @if($post->tags->count() > 0)
                            @foreach($post->tags as $tag)
                                <span class="badge badge-success">{{ $tag->name }}</span>
                            @endforeach
                        @else
                            <span class="text-muted">Нет тегов</span>
                        @endif
                    </div>

                    <!-- Содержание -->
                    <div class="form-group">
                        <label><strong>Содержание:</strong></label>
                        <div class="border p-3 mt-2" style="background-color: #f8f9fa; min-height: 200px;">
                            {!! nl2br(e($post->content)) !!}
                        </div>
                    </div>

                    <!-- Изображение (если есть) -->
                    @if($post->image)
                    <div class="form-group">
                        <label><strong>Изображение:</strong></label>
                        <div>
                           <x-post-image :post="$post" 
                                        class="img-fluid" 
                                        style="max-width: 400px;" 
                                        :clickable="false"
                                        :showPlaceholder="false" />
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Боковая панель со статистикой -->
        <div class="col-md-4">
            <!-- Статистика -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Статистика</h3>
                </div>
                <div class="card-body">
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="fas fa-comments"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Комментариев</span>
                            <span class="info-box-number">{{ $post->comments->count() }}</span>
                        </div>
                    </div>

                    <div class="info-box">
                        <span class="info-box-icon bg-success"><i class="fas fa-tags"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Тегов</span>
                            <span class="info-box-number">{{ $post->tags->count() }}</span>
                        </div>
                    </div>

                    <div class="info-box">
                        <span class="info-box-icon bg-warning"><i class="fas fa-calendar"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Последнее изменение</span>
                            <span class="info-box-number" style="font-size: 14px;">
                                {{ $post->updated_at->format('d.m.Y H:i') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Действия -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Действия</h3>
                </div>
                <div class="card-body">
                    <div class="btn-group-vertical w-100">
                        <a href="{{ route('admin.posts.edit', $post) }}" class="btn btn-warning mb-2">
                            <i class="fas fa-edit"></i> Редактировать пост
                        </a>
                        <a href="{{ route('posts.show', $post) }}" class="btn btn-info mb-2" target="_blank">
                            <i class="fas fa-external-link-alt"></i> Просмотр на сайте
                        </a>
                        <form action="{{ route('admin.posts.destroy', $post) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Вы уверены, что хотите удалить этот пост? Это действие нельзя отменить!')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fas fa-trash"></i> Удалить пост
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Комментарии к посту -->
    @if($post->comments->count() > 0)
    <div class="card mt-4">
        <div class="card-header">
            <h3 class="card-title">Комментарии к посту ({{ $post->comments->count() }})</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Автор</th>
                            <th>Содержание</th>
                            <th>Дата</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($post->comments->take(5) as $comment)
                        <tr>
                            <td>{{ $comment->author_name ?: 'Аноним' }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($comment->content, 80) }}</td>                            <td>
                                <a href="{{ route('admin.comments.show', $comment) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.comments.edit', $comment) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($post->comments->count() > 5)
                <div class="text-center">
                    <a href="{{ route('admin.comments.index', ['post_id' => $post->id]) }}" class="btn btn-primary">
                        Показать все комментарии
                    </a>
                </div>
            @endif
        </div>
    </div>
    @endif
@stop

@section('css')
    <style>
        .badge-lg {
            font-size: 0.9em;
            padding: 0.5em 0.8em;
        }
        .info-box {
            margin-bottom: 15px;
        }
        .btn-group-vertical .btn {
            text-align: left;
        }
    </style>
@stop

@section('js')
    <script>
        console.log('Post details page loaded');
    </script>
@stop