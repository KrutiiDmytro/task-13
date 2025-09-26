@extends('layouts.app')

@section('title', 'Поиск' . ($q ? ': ' . $q : ''))

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1 class="mb-4">
                    @if($q)
                        Результаты поиска по запросу: "{{ $q }}"
                    @else
                        Поиск
                    @endif
                </h1>
                
                {{-- Форма поиска --}}
                <form method="GET" action="{{ route('public.search') }}" class="mb-4">
                    <div class="input-group">
                        <input type="text" name="q" class="form-control" placeholder="Введите поисковый запрос..." value="{{ $q }}">
                        <button class="btn btn-primary" type="submit">Найти</button>
                    </div>
                </form>
                
                @if($posts->count() > 0)
                    <p class="text-muted mb-3">Найдено {{ $posts->total() }} {{ Str::plural('результат', $posts->total(), ['результат', 'результата', 'результатов']) }}</p>
                    
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
                                            @if($post->category)
                                                <small class="text-muted">
                                                    Категория: 
                                                    <a href="{{ route('public.category', $post->category->slug ?? $post->category->id) }}" class="text-decoration-none">
                                                        {{ $post->category->name }}
                                                    </a>
                                                </small>
                                                <br>
                                            @endif
                                            @if($post->tags && $post->tags->count() > 0)
                                                <small class="text-muted">
                                                    Теги: 
                                                    @foreach($post->tags as $tag)
                                                        <a href="{{ route('public.tag', $tag->slug ?? $tag->id) }}" class="badge bg-secondary text-decoration-none me-1">
                                                            {{ $tag->name }}
                                                        </a>
                                                    @endforeach
                                                </small>
                                                <br>
                                            @endif
                                            <small class="text-muted">{{ $post->published_at?->format('d.m.Y') ?? $post->created_at->format('d.m.Y') }}</small>
                                        </div>
                                        
                                        <div class="mt-2">
                                            <a href="{{ route('public.post', $post->slug ?? $post->id) }}" class="btn btn-primary btn-sm">Читать далее</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="d-flex justify-content-center">
                        {{ $posts->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="alert alert-info">
                        <h4>Ничего не найдено</h4>
                        @if($q)
                            <p>По запросу "{{ $q }}" ничего не найдено. Попробуйте изменить критерии поиска.</p>
                        @else
                            <p>Введите поисковый запрос выше для поиска по сайту.</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection