@extends('layouts.app')

@section('title', 'Редактировать пост')

@section('content')
    <h1>Редактировать пост: {{ $post->title }}</h1>

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
            <form action="{{ route('posts.update', $post) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="mb-3">
                    <label for="title" class="form-label">Заголовок</label>
                    <input type="text" id="title" name="title" class="form-control" value="{{ old('title', $post->title) }}" required>
                </div>
                
                <div class="mb-3">
                    <label for="content" class="form-label">Содержание</label>
                    <textarea id="content" name="content" class="form-control" rows="10" required>{{ old('content', $post->content) }}</textarea>
                </div>
                
                <div class="mb-3">
                    <label for="category_id" class="form-label">Категория</label>
                    <select id="category_id" name="category_id" class="form-control">
                        <option value="">Без категории</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $post->category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="tags" class="form-label">Теги</label>
                    <select id="tags" name="tags[]" class="form-control" multiple>
                        @foreach($tags as $tag)
                            <option value="{{ $tag->id }}" {{ in_array($tag->id, old('tags', $post->tags->pluck('id')->toArray())) ? 'selected' : '' }}>
                                {{ $tag->name }}
                            </option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted">Удерживайте Ctrl (Cmd на Mac) для выбора нескольких тегов</small>
                </div>
                
                <div class="mb-3">
                    <label for="image" class="form-label">Изображение</label>
                    @if($post->image)
                        <div class="mb-2">
                            <img src="{{ Storage::url($post->image) }}" alt="Текущее изображение" class="img-thumbnail" style="max-height: 200px;">
                            <p class="text-muted small">Текущее изображение</p>
                        </div>
                    @endif
                    <input type="file" id="image" name="image" class="form-control" accept="image/*">
                    <small class="form-text text-muted">Поддерживаемые форматы: JPEG, PNG, JPG, GIF. Максимальный размер: 2MB</small>
                </div>
                
                <button type="submit" class="btn btn-primary">Обновить пост</button>
                <a href="{{ route('posts.show', $post) }}" class="btn btn-secondary">Отмена</a>
            </form>
        </div>
    </div>
@endsection