@extends('adminlte::page')

@section('title', 'Создать пользователя')

@section('content_header')
    <h1>Создать пользователя</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Новый пользователь</h3>
            <div class="card-tools">
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Назад к списку
                </a>
            </div>
        </div>
        
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf
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
                                   value="{{ old('name') }}" 
                                   required
                                   placeholder="Введите имя пользователя...">
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
                                   value="{{ old('email') }}" 
                                   required
                                   placeholder="email@example.com">
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
                            <label for="password">Пароль <span class="text-danger">*</span></label>
                            <input type="password" 
                                   name="password" 
                                   id="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   required
                                   placeholder="Минимум 8 символов">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <!-- Подтверждение пароля -->
                        <div class="form-group">
                            <label for="password_confirmation">Подтвердите пароль <span class="text-danger">*</span></label>
                            <input type="password" 
                                   name="password_confirmation" 
                                   id="password_confirmation" 
                                   class="form-control" 
                                   required
                                   placeholder="Повторите пароль">
                        </div>
                    </div>
                </div>

                <!-- Роли -->
                <div class="form-group">
                    <label for="roles">Роли</label>
                    <select name="roles[]" id="roles" class="form-control" multiple>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" 
                                    {{ in_array($role->name, old('roles', [])) ? 'selected' : '' }}>
                                {{ ucfirst($role->name) }}
                            </option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted">
                        Держите Ctrl (Cmd на Mac) для выбора нескольких ролей
                    </small>
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Создать пользователя
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Отмена
                </a>
            </div>
        </form>
    </div>
@stop

@section('css')
    <style>
        .form-group label {
            font-weight: 600;
        }
        .text-danger {
            color: #dc3545 !important;
        }
    </style>
@stop

@section('js')
    <script>
        // Автофокус на поле имени
        document.getElementById('name').focus();
        
        // Валидация паролей
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const passwordConfirm = document.getElementById('password_confirmation').value;
            
            if (password !== passwordConfirm) {
                alert('Пароли не совпадают!');
                e.preventDefault();
                return false;
            }
            
            if (password.length < 8) {
                alert('Пароль должен содержать минимум 8 символов!');
                e.preventDefault();
                return false;
            }
        });
    </script>
@stop