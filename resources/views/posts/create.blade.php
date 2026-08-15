@extends('layouts.app')

@section('title', 'Создать пост')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Создать новый пост</h4>
                </div>
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

                    <form action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- Поля для неавторизованных пользователей -->
                        @guest
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="author_name" class="form-label">Ваше имя <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control @error('author_name') is-invalid @enderror"
                                       id="author_name"
                                       name="author_name"
                                       value="{{ old('author_name') }}"
                                       required
                                       placeholder="Введите ваше имя">
                                @error('author_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="author_email" class="form-label">Email (необязательно)</label>
                                <input type="email"
                                       class="form-control @error('author_email') is-invalid @enderror"
                                       id="author_email"
                                       name="author_email"
                                       value="{{ old('author_email') }}"
                                       placeholder="email@example.com">
                                @error('author_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        @endguest

                        <!-- Заголовок поста -->
                        <div class="mb-3">
                            <label for="title" class="form-label">Заголовок <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control @error('title') is-invalid @enderror"
                                   id="title"
                                   name="title"
                                   value="{{ old('title') }}"
                                   required
                                   placeholder="Введите заголовок поста">
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Категория -->
                        <div class="mb-3">
                            <label for="category_id" class="form-label">Категория</label>
                            <select class="form-control" id="category_id" name="category_id">
                                <option value="">Выберите категорию</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Теги -->
                        <div class="mb-3">
                            <label for="tags" class="form-label">Теги</label>
                            <select class="form-control" id="tags" name="tags[]" multiple>
                                @foreach($tags as $tag)
                                    <option value="{{ $tag->id }}" {{ in_array($tag->id, old('tags', [])) ? 'selected' : '' }}>
                                        {{ $tag->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">
                                Держите Ctrl для выбора нескольких тегов или введите новые через запятую
                            </small>
                        </div>

                        <!-- Содержание -->
                        <div class="mb-3">
                            <label for="content" class="form-label">Содержание <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('content') is-invalid @enderror"
                                      id="content"
                                      name="content"
                                      rows="10"
                                      required
                                      placeholder="Напишите содержание поста...">{{ old('content') }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('posts.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Назад
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Создать пост
                            </button>
                        </div>

                        <div class="form-group">
                            <label for="image">Изображение</label>
                            <input type="file" class="form-control @error('image') is-invalid @enderror"
                                id="image" name="image" accept=".jpg,.jpeg,.png,.webp,.gif">
                            @error('image')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Инициализируем Select2 для тегов
    $('#tags').select2({
        tags: true,
        tokenSeparators: [','],
        placeholder: 'Выберите или введите теги...'
    });
});
</script>
@endpush
