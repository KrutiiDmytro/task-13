@extends('adminlte::page')

@section('title', 'Редактировать пользователя')

@section('content_header')
    <h1>Редактировать пользователя</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Редактирование: {{ $user->name }}</h3>
            <div class="card-tools">
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Назад к списку
                </a>
                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-info btn-sm">
                    <i class="fas fa-eye"></i> Просмотр
                </a>
            </div>
        </div>

        <form action="{{ route('admin.users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-6">
                        <!-- Имя -->
                        <div class="form-group">
                            <label for="name">Имя <span class="text-danger">*</span></label>
                            <input type="text"
                                   name="name"
                                   id="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $user->name) }}"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <!-- Email -->
                        <div class="form-group">
                            <label for="email">Email <span class="text-danger">*</span></label>
                            <input type="email"
                                   name="email"
                                   id="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $user->email) }}"
                                   required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <!-- Пароль -->
                        <div class="form-group">
                            <label for="password">Новый пароль</label>
                            <input type="password"
                                   name="password"
                                   id="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   placeholder="Оставьте пустым, чтобы не менять">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Минимум 8 символов. Оставьте пустым, если не хотите менять пароль.
                            </small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <!-- Подтверждение пароля -->
                        <div class="form-group">
                            <label for="password_confirmation">Подтвердите пароль</label>
                            <input type="password"
                                   name="password_confirmation"
                                   id="password_confirmation"
                                   class="form-control"
                                   placeholder="Повторите новый пароль">
                        </div>
                    </div>
                </div>

                <!-- Роли -->
                <div class="form-group">
                    <label for="roles">Роли</label>
                    <select name="roles[]" id="roles" class="form-control" multiple>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}"
                                    {{ in_array($role->name, old('roles', $user->roles->pluck('name')->toArray())) ? 'selected' : '' }}>
                                {{ ucfirst($role->name) }}
                            </option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted">
                        Держите Ctrl (Cmd на Mac) для выбора нескольких ролей
                    </small>
                </div>

                <!-- Статистика -->
                <div class="alert alert-info">
                    <strong>Статистика:</strong> У этого пользователя {{ $user->posts->count() }} постов.
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Сохранить изменения
                </button>
                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Отмена
                </a>
            </div>
        </form>
    </div>
@stop
