@extends('adminlte::page')

@section('title', 'Просмотр комментария')

@section('content_header')
    <h1>Просмотр комментария #{{ $comment->id }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Детали комментария</h3>
            <div class="card-tools">
                <a href="{{ route('admin.comments.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Назад к списку
                </a>
                <a href="{{ route('admin.comments.edit', $comment) }}" class="btn btn-warning btn-sm">
                    <i class="fas fa-edit"></i> Редактировать
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <strong>ID:</strong> {{ $comment->id }}<br>
                    <strong>Автор:</strong> {{ $comment->name ?: 'Аноним' }}<br>
                    <strong>Email:</strong> {{ $comment->email ?: 'Не указан' }}<br>
                    <strong>Дата создания:</strong> {{ $comment->created_at->format('d.m.Y H:i:s') }}<br>
                </div>
                <div class="col-md-6">
                    <strong>Пост:</strong>
                    @if($comment->post)
                        <a href="{{ route('admin.posts.show', $comment->post) }}">
                            {{ $comment->post->title }}
                        </a>
                    @else
                        <span class="text-muted">Пост удален</span>
                    @endif
                </div>
            </div>
            <hr>
            <div>
                <strong>Содержание:</strong>
                <div class="border p-3 mt-2">
                    {{ $comment->content }}
                </div>
            </div>
        </div>
    </div>
@stop
