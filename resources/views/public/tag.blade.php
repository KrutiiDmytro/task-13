@extends('layouts.app')

@section('title', 'Тег: ' . $tag->name)

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1 class="mb-4">Тег: {{ $tag->name }}</h1>
                
                @if($posts->count() > 0)
                    <div class="row">
                        @foreach($posts as $post)
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card h-100">
                                    @if($post->image)
                                        <img src="{{ asset('storage/' . $post->image) }}" class="card-img-top" alt="{{ $post->title }}">
                                    @endif
                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title">{{ $post->title }}</h5>
                                        <p class="card-text">{{ \Illuminate\Support\Str::limit($post->content, 100) }}</p>
                                        
                                        <div class="mt-auto">
                                            <small class="text-muted">
                                                Категория: 
                                                <a href="{{ route('public.category', $post->category->slug) }}" class="text-decoration-none">
                                                    {{ $post->category->name }}
                                                </a>
                                            </small>
                                            <br>
                                            @if($post->tags->count() > 1)
                                                <small class="text-muted">
                                                    Другие теги: 
                                                    @foreach($post->tags as $postTag)
                                                        @if($postTag->id !== $tag->id)
                                                            <a href="{{ route('public.tag', $postTag->slug) }}" class="badge bg-secondary text-decoration-none me-1">
                                                                {{ $postTag->name }}
                                                            </a>
                                                        @endif
                                                    @endforeach
                                                </small>
                                                <br>
                                            @endif
                                            <small class="text-muted">{{ $post->published_at?->format('d.m.Y') }}</small>
                                        </div>
                                        
                                        <div class="mt-2">
                                            <a href="{{ route('public.post', $post->slug) }}" class="btn btn-primary btn-sm">Читать далее</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="d-flex justify-content-center">
                        {{ $posts->links() }}
                    </div>
                @else
                    <div class="alert alert-info">
                        <h4>С этим тегом пока нет постов</h4>
                        <p>Скоро здесь появится интересный контент!</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection