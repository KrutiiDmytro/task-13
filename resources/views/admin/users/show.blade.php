@extends('adminlte::page')

@section('title', 'Просмотр пользователя')

@section('content_header')
    <h1>Пользователь: {{ $user->name }}</h1>
@stop

@section('content')
    <div class="row">
        <!-- Информация о пользователе -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Информация о пользователе</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Назад к списку
                        </a>
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Редактировать
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">ID:</dt>
                        <dd class="col-sm-8">{{ $user->id }}</dd>
                        
                        <dt class="col-sm-4">Имя:</dt>
                        <dd class="col-sm-8"><strong>{{ $user->name }}</strong></dd>
                        
                        <dt class="col-sm-4">Email:</dt>
                        <dd class="col-sm-8">{{ $user->email }}</dd>
                        
                        <dt class="col-sm-4">Email подтвержден:</dt>
                        <dd class="col-sm-8">
                            @if($user->email_verified_at)
                                <span class="badge badge-success">Да</span>
                                <small class="text-muted">({{ $user->email_verified_at->format('d.m.Y H:i') }})</small>
                            @else
                                <span class="badge badge-warning">Нет</span>
                            @endif
                        </dd>
                        
                        <dt class="col-sm-4">Роли:</dt>
                        <dd class="col-sm-8">
                            @if($user->roles->count() > 0)
                                @foreach($user->roles as $role)
                                    <span class="badge badge-info">{{ ucfirst($role->name) }}</span>
                                @endforeach
                            @else
                                <span class="badge badge-secondary">Нет ролей</span>
                            @endif
                        </dd>
                        
                        <dt class="col-sm-4">Регистрация:</dt>
                        <dd class="col-sm-8">{{ $user->created_at->format('d.m.Y H:i:s') }}</dd>
                        
                        <dt class="col-sm-4">Последнее изменение:</dt>
                        <dd class="col-sm-8">{{ $user->updated_at->format('d.m.Y H:i:s') }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Статистика и действия -->
        <div class="col-md-6">
            <!-- Статистика -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Статистика</h3>
                </div>
                <div class="card-body">
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="fas fa-newspaper"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Постов</span>
                            <span class="info-box-number">{{ $user->posts->count() }}</span>
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
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning mb-2">
                            <i class="fas fa-edit"></i> Редактировать пользователя
                        </a>
                        @if($user->posts->count() > 0)
                            <a href="{{ route('admin.posts.index', ['user_id' => $user->id]) }}" class="btn btn-info mb-2">
                                <i class="fas fa-list"></i> Посты пользователя
                            </a>
                        @endif
                        @if($user->id !== auth()->id())
                            @if($user->posts->count() == 0)
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Вы уверены, что хотите удалить этого пользователя?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger w-100">
                                        <i class="fas fa-trash"></i> Удалить пользователя
                                    </button>
                                </form>
                            @else
                                <button class="btn btn-danger w-100" disabled title="Нельзя удалить пользователя с постами">
                                    <i class="fas fa-trash"></i> Удалить пользователя
                                </button>
                                <small class="text-muted mt-2">
                                    Удаление невозможно: у пользователя есть посты
                                </small>
                            @endif
                        @else
                            <button class="btn btn-danger w-100" disabled title="Нельзя удалить себя">
                                <i class="fas fa-trash"></i> Удалить пользователя
                            </button>
                            <small class="text-muted mt-2">
                                Нельзя удалить свой собственный аккаунт
                            </small>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Посты пользователя -->
    @if($user->posts->count() > 0)
    <div class="card mt-4">
        <div class="card-header">
            <h3 class="card-title">Посты пользователя ({{ $user->posts->count() }})</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Заголовок</th>
                            <th>Категория</th>
                            <th>Дата</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($user->posts->take(10) as $post)
                        <tr>
                            <td>
                                <a href="{{ route('admin.posts.show', $post) }}">
                                    {{ Str::limit($post->title, 50) }}
                                </a>
                            </td>
                            <td>
                                @if($post->category)
                                    <span class="badge badge-info">{{ $post->category->name }}</span>
                                @else
                                    <span class="badge badge-secondary">Без категории</span>
                                @endif
                            </td>
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
            @if($user->posts->count() > 10)
                <div class="text-center">
                    <a href="{{ route('admin.posts.index', ['user_id' => $user->id]) }}" class="btn btn-primary">
                        Показать все посты пользователя
                    </a>
                </div>
            @endif
        </div>
    </div>
    @endif
@stop