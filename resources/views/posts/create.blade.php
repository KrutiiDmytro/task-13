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

<form action="{{ route('posts.store') }}" method="POST">
    @csrf
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
    <div class="mb-3">
        <label class="form-label">Теги</label>
        @foreach($tags as $tag)
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="tags[]" value="{{ $tag->id }}" id="tag-{{ $tag->id }}"
                    @checked(in_array($tag->id, old('tags', [])))>
                <label class="form-check-label" for="tag-{{ $tag->id }}">
                    {{ $tag->name }}
                </label>
            </div>
        @endforeach
    </div>
    <button type="submit" class="btn btn-primary">Создать пост</button>
</form>
@endsection