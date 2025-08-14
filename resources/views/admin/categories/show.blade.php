@extends('adminlte::page')

@section('title', 'Просмотр категории')

@section('content_header')
    <h1>Категория: {{ $category->name }}</h1>
@stop

@section('content')
    <div class="row">
        <!-- Информация о категории -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Информация о категории</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Назад к списку
                        </a>
                        <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Редактировать
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">ID:</dt>
                        <dd class="col-sm-8">{{ $category->id }}</dd>
                        
                        <dt class="col-sm-4">Название:</dt>
                        <dd class="col-sm-8"><strong>{{ $category->name }}</strong></dd>
                        
                        <dt class="col-sm-4">Количество постов:</dt>
                        <dd class="col-sm-8">
                            <span class="badge badge-primary">{{ $category->posts->count() }}</span>
                        </dd>
                        
                        <dt class="col-sm-4">Создана:</dt>
                        <dd class="col-sm-8">{{ $category->created_at->format('d.m.Y H:i:s') }}</dd>
                        
                        <dt class="col-sm-4">Изменена:</dt>
                        <dd class="col-sm-8">{{ $category->updated_at->format('d.m.Y H:i:s') }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Действия -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Действия</h3>
                </div>
                <div class="card-body">
                    <div class="btn-group-vertical w-100">
                        <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-warning mb-2">
                            <i class="fas fa-edit"></i> Редактировать категорию
                        </a>
                        <a href="{{ route('admin.posts.index', ['category_id' => $category->id]) }}" class="btn btn-info mb-2">
                            <i class="fas fa-list"></i> Посты в этой категории
                        </a>
                        @if($category->posts->count() == 0)
                            <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Вы уверены, что хотите удалить эту категорию?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger w-100">
                                    <i class="fas fa-trash"></i> Удалить категорию
                                </button>
                            </form>
                        @else
                            <button class="btn btn-danger w-100" disabled title="Нельзя удалить категорию с постами">
                                <i class="fas fa-trash"></i> Удалить категорию
                            </button>
                            <small class="text-muted mt-2">
                                Удаление невозможно: в категории есть посты
                            </small>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Посты в категории -->
    @if($category->posts->count() > 0)
    <div class="card mt-4">
        <div class="card-header">
            <h3 class="card-title">Посты в категории ({{ $category->posts->count() }})</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Заголовок</th>
                            <th>Автор</th>
                            <th>Дата</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($category->posts->take(10) as $post)
                        <tr>
                            <td>
                                <a href="{{ route('admin.posts.show', $post) }}">
                                    {{ Str::limit($post->title, 50) }}
                                </a>
                            </td>
                            <td>{{ $post->user->name ?? 'Неизвестно' }}</td>
                            <td>{{ $post->created_at->format('d.m.Y H:i') }}</td>
                            <td>
                                <a href="{{ route('admin.posts.show', $post) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.posts.edit', $post) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($category->posts->count() > 10)
                <div class="text-center">
                    <a href="{{ route('admin.posts.index', ['category_id' => $category->id]) }}" class="btn btn-primary">
                        Показать все посты в категории
                    </a>
                </div>
            @endif
        </div>
    </div>
    @endif
@stop