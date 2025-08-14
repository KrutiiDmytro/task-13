@extends('adminlte::page')

@section('title', 'Создать пост')

@section('content_header')
    <h1>Создать новый пост</h1>
@stop

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Форма создания поста</h3>
        </div>
        
        <form action="{{ route('admin.posts.store') }}" method="POST">
            @csrf
            
            <div class="card-body">
                {{-- Заголовок --}}
                <div class="form-group">
                    <label for="title">Заголовок</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-heading"></i></span>
                        </div>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" 
                               id="title" name="title" placeholder="Введите заголовок поста" 
                               value="{{ old('title') }}" required>
                        @error('title')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- Содержимое --}}
                <div class="form-group">
                    <label for="content">Содержимое</label>
                    <textarea class="form-control @error('content') is-invalid @enderror" 
                              id="content" name="content" rows="10" 
                              placeholder="Введите содержимое поста" required>{{ old('content') }}</textarea>
                    @error('content')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Категория --}}
                <div class="form-group">
                    <label for="category_id">Категория</label>
                    <select class="form-control @error('category_id') is-invalid @enderror" 
                            id="category_id" name="category_id">
                        <option value="">-- Выберите категорию --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" 
                                    @selected(old('category_id') == $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Автор --}}
                <div class="form-group">
                    <label for="user_id">Автор (пользователь, необязательно)</label>
                        <select class="form-control @error('user_id') is-invalid @enderror"
                                id="user_id" name="user_id">
                        <option value="">— Оставить пустым (будет текущий пользователь) —</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" @selected(old('user_id') == $user->id)>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                        </select>
                        @error('user_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">
                            Если не выберете пользователя — автором станет текущий. Либо заполните поля ниже для гостевого автора.
                        </small>
                </div>

<div class="form-group">
    <label for="author_name">Автор (гость, имя)</label>
    <input type="text" class="form-control @error('author_name') is-invalid @enderror"
           id="author_name" name="author_name" value="{{ old('author_name') }}"
           placeholder="Имя гостевого автора">
    @error('author_name')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label for="author_email">Email гостевого автора</label>
    <input type="email" class="form-control @error('author_email') is-invalid @enderror"
           id="author_email" name="author_email" value="{{ old('author_email') }}"
           placeholder="email@example.com">
    @error('author_email')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

                {{-- Теги --}}
                <div class="form-group">
                    <label for="tags">Теги</label>
                    <select class="form-control @error('tags') is-invalid @enderror" 
                            id="tags" name="tags[]" multiple>
                        @foreach($tags as $tag)
                            <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted">Выберите существующие теги или введите новые</small>
                    @error('tags')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Создать пост
                </button>
                <a href="{{ route('admin.posts.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Отмена
                </a>
            </div>
        </form>
    </div>
@stop

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-theme@0.1.0-beta.10/dist/select2-bootstrap.min.css" rel="stylesheet" />
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#tags').select2({
                tags: true,
                tokenSeparators: [','],
                placeholder: 'Выберите или введите теги',
                theme: 'bootstrap'
            });
        });
    </script>
@stop