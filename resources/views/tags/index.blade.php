@extends('layouts.app')

@section('title', 'Все теги')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Все теги</h1>
        <a href="{{ route('tags.create') }}" class="btn btn-primary">Добавить тег</a>
    </div>

    <div class="row">
        @foreach($tags as $tag)
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">{{ $tag->name }}</h5>
                        <p class="card-text">
                            Количество постов: {{ $tag->posts_count }}
                        </p>
                        <div class="btn-group" role="group">
                            <a href="{{ route('tags.show', $tag) }}" class="btn btn-sm btn-outline-primary">Просмотр</a>
                            <a href="{{ route('tags.edit', $tag) }}" class="btn btn-sm btn-outline-warning">Редактировать</a>
                            <form action="{{ route('tags.destroy', $tag) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Удалить тег?')">Удалить</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection