@extends('adminlte::page')

@section('title', 'Создать комментарий')

@section('content_header')
    <h1>Создать комментарий</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Новый комментарий</h3>
            <div class="card-tools">
                <a href="{{ route('admin.comments.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Назад к списку
                </a>
            </div>
        </div>
        
        <form action="{{ route('admin.comments.store') }}" method="POST">
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
                        <!-- Выбор поста -->
                        <div class="form-group">
                            <label for="post_id">Пост <span class="text-danger">*</span></label>
                            <select name="post_id" id="post_id" class="form-control @error('post_id') is-invalid @enderror" required>
                                <option value="">Выберите пост...</option>
                                @foreach($posts as $post)
                                    <option value="{{ $post->id }}" {{ old('post_id') == $post->id ? 'selected' : '' }}>
                                        {{ $post->title }}
                                    </option>
                                @endforeach
                            </select>
                            @error('post_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

        <div class="col-md-3">
            <!-- Имя автора -->
            <div class="form-group">
                <label for="author">Имя автора <span class="text-danger">*</span></label>
                <input type="text" 
                        name="author" 
                        id="author" 
                        class="form-control @error('author') is-invalid @enderror" 
                        value="{{ old('author') }}"
                        required
                        placeholder="Введите имя автора">
                @error('author')
                     <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

                    <div class="col-md-3">
                        <!-- Email автора -->
                        <div class="form-group">
                            <label for="email">Email автора</label>
                            <input type="email" 
                                   name="email" 
                                   id="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   value="{{ old('email') }}"
                                   placeholder="email@example.com">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Содержание комментария -->
                <div class="form-group">
                    <label for="content">Содержание комментария <span class="text-danger">*</span></label>
                    <textarea name="content" 
                              id="content" 
                              class="form-control @error('content') is-invalid @enderror" 
                              rows="6" 
                              required
                              placeholder="Введите текст комментария...">{{ old('content') }}</textarea>
                    @error('content')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Создать комментарий
                </button>
                <a href="{{ route('admin.comments.index') }}" class="btn btn-secondary">
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
        // Автофокус на поле выбора поста
        document.getElementById('post_id').focus();
        
        // Валидация формы
        document.querySelector('form').addEventListener('submit', function(e) {
            const postId = document.getElementById('post_id').value;
            const content = document.getElementById('content').value.trim();
            
            if (!postId) {
                alert('Пожалуйста, выберите пост для комментария');
                e.preventDefault();
                return false;
            }
            
            if (!content) {
                alert('Пожалуйста, введите содержание комментария');
                e.preventDefault();
                return false;
            }
        });
    </script>
@stop