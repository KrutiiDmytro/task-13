@extends('layouts.app')

@section('title', 'Редактировать пост')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <h1 class="h4 mb-3">Редактировать пост: {{ $post->title }}</h1>

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
                            <input type="text"
                                   id="title"
                                   name="title"
                                   class="form-control @error('title') is-invalid @enderror"
                                   value="{{ old('title', $post->title) }}"
                                   required>
                            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label">Содержание</label>
                            <textarea id="content"
                                      name="content"
                                      rows="10"
                                      class="form-control @error('content') is-invalid @enderror"
                                      required>{{ old('content', $post->content) }}</textarea>
                            @error('content') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="category_id" class="form-label">Категория</label>
                            <select id="category_id" name="category_id" class="form-control @error('category_id') is-invalid @enderror">
                                <option value="">Без категории</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $post->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="tags" class="form-label">Теги</label>
                            <select id="tags" name="tags[]" class="form-control @error('tags') is-invalid @enderror" multiple>
                                @php($selected = old('tags', $post->tags->pluck('id')->toArray()))
                                @foreach($tags as $tag)
                                    <option value="{{ $tag->id }}" {{ in_array($tag->id, $selected) ? 'selected' : '' }}>
                                        {{ $tag->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tags') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <small class="text-muted">Удерживайте Ctrl (Cmd на Mac) для выбора нескольких тегов.</small>
                        </div>

                        @if($post->image)
                            <div class="mb-3">
                                <p class="form-label">Текущее изображение</p>
                                <div>
                                    <a href="{{ Storage::url($post->image) }}" target="_blank" rel="noopener">
                                        <img src="{{ Storage::url($post->image) }}"
                                             class="img-fluid rounded border"
                                             style="max-height:200px; object-fit:cover;"
                                             alt="{{ $post->title }}">
                                    </a>
                                </div>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label for="image" class="form-label">Заменить изображение</label>
                            <input type="file"
                                   id="image"
                                   name="image"
                                   class="form-control @error('image') is-invalid @enderror"
                                   accept=".jpg,.jpeg,.png,.webp,.gif">
                            @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <small class="text-muted">Если файл не выбрать — останется текущее изображение.</small>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Обновить пост</button>
                            <a href="{{ route('posts.show', $post) }}" class="btn btn-secondary">Отмена</a>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
