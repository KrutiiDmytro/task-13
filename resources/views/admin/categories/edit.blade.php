@extends('adminlte::page')

@section('title', 'Редактировать категорию')

@section('content_header')
    <h1>Редактировать категорию</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Редактирование категории: {{ $category->name }}</h3>
            <div class="card-tools">
                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Назад к списку
                </a>
                <a href="{{ route('admin.categories.show', $category) }}" class="btn btn-info btn-sm">
                    <i class="fas fa-eye"></i> Просмотр
                </a>
            </div>
        </div>
        
        <form action="{{ route('admin.categories.update', $category) }}" method="POST">
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

                <!-- Название категории -->
                <div class="form-group">
                    <label for="name">Название категории <span class="text-danger">*</span></label>
                    <input type="text" 
                           name="name" 
                           id="name" 
                           class="form-control @error('name') is-invalid @enderror" 
                           value="{{ old('name', $category->name) }}" 
                           required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Статистика -->
                <div class="alert alert-info">
                    <strong>Статистика:</strong> В этой категории {{ $category->posts->count() }} постов.
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Сохранить изменения
                </button>
                <a href="{{ route('admin.categories.show', $category) }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Отмена
                </a>
            </div>
        </form>
    </div>
@stop