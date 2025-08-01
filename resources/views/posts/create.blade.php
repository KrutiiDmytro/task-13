@extends('layouts.app')

@section('title', 'Создать новый пост')

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

<form action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    {{-- Поля для заголовка, содержимого и категории --}}
    <div class="mb-3">
        <label for="title" class="form-label">Заголовок</label>
        <input type="text" id="title" name="title" class="form-control" value="{{ old('title') }}" required>
    </div>
    <div class="mb-3">
        <label for="content" class="form-label">Содержимое</label>
        <textarea id="content" name="content" class="form-control" rows="10" required>{{ old('content') }}</textarea>
    </div>
    <div class="mb-3">
        <label for="category_id" class="form-label">Категория</label>
        <select id="category_id" name="category_id" class="form-select">
            <option value="">Без категории</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Блок для тегов --}}
    <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <label class="form-label mb-0">Теги</label>
            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#createTagModal">
                Создать новый тег
            </button>
        </div>
        <div class="border rounded p-2 mt-2" id="tags-container" style="max-height: 200px; overflow-y: auto;">
            @foreach($tags as $tag)
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="tags[]" value="{{ $tag->id }}" id="tag-{{ $tag->id }}" @checked(in_array($tag->id, old('tags', [])))>
                    <label class="form-check-label" for="tag-{{ $tag->id }}">
                        {{ $tag->name }}
                    </label>
                </div>
            @endforeach
        </div>
    </div>
    
    {{-- Поле для изображения --}}
    <div class="mb-3">
        <label for="image" class="form-label">Изображение</label>
        <input type="file" id="image" name="image" class="form-control" accept="image/*">
    </div>
    
    <button type="submit" class="btn btn-primary">Создать пост</button>
    <a href="{{ route('posts.index') }}" class="btn btn-secondary">Отмена</a>
</form>


{{-- Модальное окно для создания тега --}}
<div class="modal fade" id="createTagModal" tabindex="-1" aria-labelledby="createTagModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="createTagModalLabel">Создать новый тег</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        {{-- Передаем URL в JS через data-атрибут --}}
        <form id="createTagForm" data-store-url="{{ route('tags.store.ajax') }}">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <div class="mb-3">
                <label for="newTagName" class="form-label">Название тега</label>
                <input type="text" class="form-control" id="newTagName" name="name" required>
                <div id="tagNameError" class="invalid-feedback"></div>
            </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
        <button type="button" class="btn btn-primary" id="saveTagButton">Сохранить тег</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
    {{-- Подключаем наш новый JS файл --}}
    <script src="{{ asset('js/tag-creator.js') }}" defer></script>
@endpush