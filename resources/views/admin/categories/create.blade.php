@extends('adminlte::page')

@section('title', 'Создать категорию')

@section('content_header')
    <h1>Создать категорию</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Новая категория</h3>
            <div class="card-tools">
                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Назад к списку
                </a>
            </div>
        </div>
        
        <form action="{{ route('admin.categories.store') }}" method="POST">
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

                <!-- Название категории -->
                <div class="form-group">
                    <label for="name">Название категории <span class="text-danger">*</span></label>
                    <input type="text" 
                           name="name" 
                           id="name" 
                           class="form-control @error('name') is-invalid @enderror" 
                           value="{{ old('name') }}" 
                           required
                           placeholder="Введите название категории...">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">
                        Название должно быть уникальным и понятным для пользователей.
                    </small>
                </div>

                <!-- Описание категории (если в модели есть поле description) -->
                <div class="form-group">
                    <label for="description">Описание (необязательно)</label>
                    <textarea name="description" 
                              id="description" 
                              class="form-control @error('description') is-invalid @enderror" 
                              rows="4"
                              placeholder="Краткое описание категории...">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Создать категорию
                </button>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
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
        // Автофокус на поле названия
        document.getElementById('name').focus();
        
        // Валидация формы
        document.querySelector('form').addEventListener('submit', function(e) {
            const name = document.getElementById('name').value.trim();
            
            if (!name) {
                alert('Пожалуйста, введите название категории');
                e.preventDefault();
                return false;
            }
        });
    </script>
@stop