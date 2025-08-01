@extends('layouts.app')

@section('title', 'Редактировать тег')

@section('content')
    <h1>Редактировать тег: {{ $tag->name }}</h1>

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
            <form action="{{ route('tags.update', $tag) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label for="name" class="form-label">Название тега</label>
                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $tag->name) }}" required>
                </div>
                <button type="submit" class="btn btn-primary">Обновить тег</button>
                <a href="{{ route('tags.index') }}" class="btn btn-secondary">Отмена</a>
            </form>
        </div>
    </div>
@endsection