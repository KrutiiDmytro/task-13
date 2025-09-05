@extends('adminlte::page')

@section('title', 'Просмотр поста')

@section('content_header')
    <h1>{{ $post->title }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="card-tools">
                <a href="{{ route('admin.posts.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Назад к списку
                </a>
                <a href="{{ route('admin.posts.edit', $post) }}" class="btn btn-warning btn-sm">
                    <i class="fas fa-edit"></i> Редактировать
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <h3>{{ $post->title }}</h3>
                    
                    @if($post->image)
                        <img src="{{ Storage::url($post->image) }}" class="img-fluid mb-3" alt="{{ $post->title }}">
                    @endif
                    
                    <div class="content">
                        {!! nl2br(e($post->content)) !!}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Информация</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Автор:</strong> {{ $post->user->name ?? $post->author_name ?? 'Аноним' }}</p>
                            <p><strong>Категория:</strong> {{ $post->category->name ?? 'Без категории' }}</p>
                            <p><strong>Дата:</strong> {{ $post->date->format('d.m.Y') }}</p>
                            
                            @if($post->tags->count() > 0)
                                <p><strong>Теги:</strong></p>
                                @foreach($post->tags as $tag)
                                    <span class="badge bg-secondary">{{ $tag->name }}</span>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop