@extends('adminlte::page')

@section('title', 'Редактировать пост')

@section('content_header')
    <h1>Редактировать пост</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Редактирование поста: {{ $post->title }}</h3>
            <div class="card-tools">
                <a href="{{ route('admin.posts.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Назад к списку
                </a>
                <a href="{{ route('admin.posts.show', $post) }}" class="btn btn-info btn-sm">
                    <i class="fas fa-eye"></i> Просмотр
                </a>
            </div>
        </div>
        
        <form action="{{ route('admin.posts.update', $post) }}" method="POST">
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

                <!-- Заголовок -->
                <div class="form-group">
                    <label for="title">Заголовок <span class="text-danger">*</span></label>
                    <input type="text" 
                           name="title" 
                           id="title" 
                           class="form-control @error('title') is-invalid @enderror" 
                           value="{{ old('title', $post->title) }}" 
                           required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <!-- Категория -->
                        <div class="form-group">
                            <label for="category_id">Категория</label>
                            <select name="category_id" id="category_id" class="form-control">
                                <option value="">Без категории</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" 
                                            {{ old('category_id', $post->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <!-- Теги -->
                        <div class="form-group">
                            <label for="tags">Теги</label>
                            <select name="tags[]" id="tags" class="form-control" multiple>
                                @foreach($tags as $tag)
                                    <option value="{{ $tag->id }}" 
                                            {{ in_array($tag->id, old('tags', $post->tags->pluck('id')->toArray())) ? 'selected' : '' }}>
                                        {{ $tag->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">
                                Держите Ctrl (Cmd на Mac) для выбора нескольких тегов, или введите новые теги через запятую
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Содержание -->
                <div class="form-group">
                    <label for="content">Содержание <span class="text-danger">*</span></label>
                    <textarea name="content" 
                              id="content" 
                              class="form-control @error('content') is-invalid @enderror" 
                              rows="15" 
                              required>{{ old('content', $post->content) }}</textarea>
                    @error('content')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Сохранить изменения
                </button>
                <a href="{{ route('admin.posts.show', $post) }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Отмена
                </a>
            </div>
        </form>
    </div>
@stop

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet" />
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Инициализируем Select2 для тегов
            $('#tags').select2({
                tags: true,
                tokenSeparators: [','],
                placeholder: 'Выберите теги или введите новые...'
            });
        });
    </script>
@stop