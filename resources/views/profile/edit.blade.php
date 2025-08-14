@extends('layouts.app')

@section('title', 'Профиль')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Профиль пользователя</h4>
                </div>
                <div class="card-body">
                    <!-- Форма обновления профиля -->
                    <form method="POST" action="{{ route('profile.update') }}">
                        @csrf
                        @method('patch')
                        
                        @if (session('status') === 'profile-updated')
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                Профиль успешно обновлен!
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Имя</label>
                                <input type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name', $user->name) }}" 
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email', $user->email) }}" 
                                       required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                            <div class="alert alert-warning">
                                <p class="mb-2">Ваш email адрес не подтвержден.</p>
                                <form method="POST" action="{{ route('verification.send') }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-link p-0">
                                        Нажмите здесь, чтобы повторно отправить письмо с подтверждением.
                                    </button>
                                </form>
                            </div>
                        @endif

                        <div class="d-flex justify-content-between">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Сохранить
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Изменение пароля -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Изменить пароль</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf
                        @method('put')

                        @if (session('status') === 'password-updated')
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                Пароль успешно изменен!
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label for="current_password" class="form-label">Текущий пароль</label>
                            <input type="password" 
                                   class="form-control @error('current_password') is-invalid @enderror" 
                                   id="current_password" 
                                   name="current_password">
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label for="password" class="form-label">Новый пароль</label>
                                <input type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       id="password" 
                                       name="password">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label">Подтвердите пароль</label>
                                <input type="password" 
                                       class="form-control" 
                                       id="password_confirmation" 
                                       name="password_confirmation">
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-key"></i> Изменить пароль
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Удаление аккаунта -->
            <div class="card mt-4 border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">Опасная зона</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        После удаления вашего аккаунта все его ресурсы и данные будут удалены безвозвратно. 
                        Пожалуйста, введите ваш пароль для подтверждения удаления аккаунта.
                    </p>
                    
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                        <i class="fas fa-trash"></i> Удалить аккаунт
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно удаления аккаунта -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Удаление аккаунта</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('profile.destroy') }}">
                @csrf
                @method('delete')
                <div class="modal-body">
                    <p>Вы уверены, что хотите удалить свой аккаунт?</p>
                    <p class="text-muted">
                        Після видалення вашого акаунта всі його ресурси і дані будуть видалені безповоротно. 
                        Будь ласка, введіть ваш пароль для підтвердження видалення акаунта.
                    </p>
                    
                    <div class="mb-3">
                        <label for="delete_password" class="form-label">Пароль</label>
                        <input type="password" 
                               class="form-control @error('password', 'userDeletion') is-invalid @enderror" 
                               id="delete_password" 
                               name="password" 
                               placeholder="Введите ваш пароль">
                        @error('password', 'userDeletion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-danger">Удалить аккаунт</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection