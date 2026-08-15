@extends('adminlte::page')

@section('title', 'Управление пользователями')

@section('content_header')
    <h1>Управление пользователями</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Список пользователей</h3>
            <div class="card-tools">
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Создать пользователя
                </a>
            </div>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($users->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Имя</th>
                                <th>Email</th>
                                <th>Роли</th>
                                <th>Постов</th>
                                <th>Регистрация</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>
                                        <strong>{{ $user->name }}</strong>
                                        @if($user->id === auth()->id())
                                            <span class="badge badge-success">Вы</span>
                                        @endif
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @if($user->roles && $user->roles->count() > 0)
                                            @foreach($user->roles as $role)
                                                <span class="badge badge-info">{{ $role->name }}</span>
                                            @endforeach
                                        @else
                                            <span class="badge badge-secondary">Нет ролей</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-primary">{{ $user->posts_count ?? 0 }}</span>
                                    </td>
                                    <td>{{ $user->created_at->format('d.m.Y') }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.users.show', $user) }}"
                                               class="btn btn-info btn-sm" title="Просмотр">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.users.edit', $user) }}"
                                               class="btn btn-warning btn-sm" title="Редактировать">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if($user->id !== auth()->id())
                                                <form action="{{ route('admin.users.destroy', $user) }}"
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('Вы уверены, что хотите удалить этого пользователя?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" title="Удалить">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <button class="btn btn-danger btn-sm" disabled title="Нельзя удалить себя">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Пагинация -->
                <div class="d-flex justify-content-center">
                    {{ $users->links() }}
                </div>
            @else
                <div class="text-center">
                    <div class="alert alert-info">
                        <h4><i class="fas fa-info-circle"></i> Пользователей пока нет</h4>
                        <p>Создайте первого пользователя.</p>
                        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Создать первого пользователя
                        </a>
                    </div>
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
