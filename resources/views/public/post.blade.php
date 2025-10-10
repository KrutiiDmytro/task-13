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
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Навигация</h5>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('home') }}" class="btn btn-outline-primary btn-sm mb-2 d-block">← Все посты</a>
                        <a href="{{ route('public.category', $post->category->slug) }}" class="btn btn-outline-secondary btn-sm d-block">
                            Другие посты в категории "{{ $post->category->name }}"
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection