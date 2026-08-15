@extends('layouts.app')

@section('title', 'Создать комментарий')

@section('content')
    <h1>Создать новый комментарий</h1>

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
            {{-- форма создаёт комментарий --}}
            <form action="{{ route('comments.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="author" class="form-label">Автор</label>
                    <input type="text"
                           id="author"
                           name="author"
                           class="form-control"
                           value="{{ old('author') }}"
                           required>
                </div>

                <div class="mb-3">
                    <label for="content" class="form-label">Комментарий</label>
                    <textarea id="content"
                              name="content"
                              class="form-control"
                              rows="4"
                              required>{{ old('content') }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="post_id" class="form-label">К какому посту?</label>
                    <select id="post_id" name="post_id" class="form-select" required>
                        <option value="">Выберите пост</option>
                        @foreach($posts as $post)
                            <option value="{{ $post->id }}"
                                    @selected(old('post_id') == $post->id)>
                                {{ $post->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Создать комментарий</button>
                <a href="{{ route('comments.index') }}" class="btn btn-secondary">Отмена</a>
            </form>
        </div>
    </div>
@endsection
