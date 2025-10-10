@extends('layouts.app')

@section('title', $post->title)

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8">
                <article>
                    <header class="mb-4">
                        <h1>{{ $post->title }}</h1>
                        <div class="text-muted mb-3">
                            <small>
                                Опубликовано: {{ $post->published_at?->format('d.m.Y H:i') ?? $post->created_at->format('d.m.Y H:i') }}
                                @if($post->author_name)
                                    | Автор: {{ $post->author_name }}
                                @endif
                            </small>
                        </div>

                        {{-- изображение --}}
                        <x-post-image :post="$post" 
                                    class="img-fluid rounded mb-4" 
                                    :showPlaceholder="false" />
                    </header>
                    
                    <div class="content">
                        {!! nl2br(e($post->content)) !!}
                    </div>
                    
                    <footer class="mt-4 pt-3 border-top">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Категория:</strong>
                                <a href="{{ route('public.category', $post->category->slug) }}" class="text-decoration-none">
                                    {{ $post->category->name }}
                                </a>
                            </div>
                            
                            @if($post->tags->count() > 0)
                                <div class="col-md-6">
                                    <strong>Теги:</strong>
                                    @foreach($post->tags as $tag)
                                        <a href="{{ route('public.tag', $tag->slug) }}" class="badge bg-primary text-decoration-none me-1">
                                            {{ $tag->name }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </footer>
                </article>

                                {{-- Секция комментариев --}}
                <div class="mt-5">
                    <h3>Комментарии ({{ $post->comments->count() }})</h3>

                    {{-- Список комментариев --}}
                    @if($post->comments->count())
                        @foreach($post->comments as $comment)
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6 class="card-subtitle mb-2 text-muted">
                                        {{ $comment->author_name ?? 'Аноним' }}
                                    </h6>
                                    <p class="card-text mb-1">{{ $comment->content }}</p>
                                    <small class="text-muted">
                                        {{ $comment->created_at->format('d.m.Y H:i') }}
                                    </small>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted">Пока нет комментариев. Будьте первым!</p>
                    @endif

                    {{-- Форма добавления комментария --}}
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="mb-0">Добавить комментарий</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('comments.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="post_id" value="{{ $post->id }}">

                                <div class="mb-3">
                                    <label for="author" class="form-label">
                                        Ваше имя <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('author') is-invalid @enderror" 
                                           id="author" 
                                           name="author" 
                                           value="{{ old('author', auth()->user()->name ?? '') }}" 
                                           required>
                                    @error('author')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="content" class="form-label">
                                        Комментарий <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control @error('content') is-invalid @enderror" 
                                              id="content" 
                                              name="content" 
                                              rows="4" 
                                              required>{{ old('content') }}</textarea>
                                    @error('content')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-1"></i> Отправить комментарий
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
@endsection