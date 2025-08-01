@extends('layouts.app')

@section('title', 'Создать пост')

@section('content')
    <h1>Создать новый пост</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="title" class="form-label">Заголовок</label>
                    <input type="text" id="title" name="title" class="form-control" value="{{ old('title') }}" required>
                </div>
                
                <div class="mb-3">
                    <label for="content" class="form-label">Содержание</label>
                    <textarea id="content" name="content" class="form-control" rows="10" required>{{ old('content') }}</textarea>
                </div>
                
                <div class="mb-3">
                    <label for="category_id" class="form-label">Категория</label>
                    <select id="category_id" name="category_id" class="form-control">
                        <option value="">Без категории</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="tags" class="form-label">Теги</label>
                    <select id="tags" name="tags[]" class="form-control" multiple>
                        @foreach($tags as $tag)
                            <option value="{{ $tag->id }}" {{ in_array($tag->id, old('tags', [])) ? 'selected' : '' }}>
                                {{ $tag->name }}
                            </option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted">Удерживайте Ctrl (Cmd на Mac) для выбора нескольких тегов</small>
                </div>
                
                <div class="mb-3">
                    <label for="image" class="form-label">Изображение</label>
                    <input type="file" id="image" name="image" class="form-control" accept="image/*">
                    <small class="form-text text-muted">Поддерживаемые форматы: JPEG, PNG, JPG, GIF. Максимальный размер: 2MB</small>
                </div>
                
                <button type="submit" class="btn btn-primary">Создать пост</button>
                <a href="{{ route('posts.index') }}" class="btn btn-secondary">Отмена</a>
            </form>
        </div>
    </div>
@endsection