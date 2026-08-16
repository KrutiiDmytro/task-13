@extends('layouts.app')

@section('title', 'Blog')

@section('content')
<div class="container py-4">
    <div class="row">
        <!-- Main content -->
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Blog</h1>
                <a href="{{ route('posts.create') }}" class="btn btn-create">
                    <i class="fas fa-plus me-2"></i>New post
                </a>
            </div>

            @if($posts->count() > 0)
                @foreach($posts as $post)
                    <div class="card post-card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">
                                <a href="{{ route('posts.show', $post) }}" class="text-decoration-none">
                                    {{ $post->title }}
                                </a>
                            </h5>

                            <p class="card-text">{{ Str::limit($post->content, 200) }}</p>

                            {{-- Tags, shown only when the post has any --}}
                            @if($post->tags->count())
                                <div class="mt-2 mb-2">
                                    <span class="text-muted me-2">Tags:</span>
                                    @foreach($post->tags as $tag)
                                        <a href="{{ route('posts.index', array_filter([
                                            'search'   => request('search'),
                                            'category' => request('category'),
                                            'tag'      => $tag->id,
                                        ])) }}" class="badge bg-secondary text-decoration-none me-2">#{{ $tag->name }}</a>
                                    @endforeach
                                </div>
                            @endif

                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    {{ $post->date->format('d.m.Y') }} |
                                    By {{ $post->user->name ?? $post->author_name ?? 'Anonymous' }}
                                    @if($post->category)
                                        | <a href="{{ route('posts.index', ['category' => $post->category->id]) }}">{{ $post->category->name }}</a>
                                    @endif
                                </small>

                                <!-- Кнопки управления - только для авторизованных владельцев -->
                                @auth
                                    @if(auth()->id() === $post->user_id || (method_exists(auth()->user(),'hasRole') ? auth()->user()->hasRole('admin') : (auth()->user()->is_admin ?? false)))
                                        <div class="btn-group" role="group" aria-label="Actions for post: {{ $post->title }}">
                                            <a href="{{ route('posts.edit', $post) }}" class="btn btn-edit btn-sm"
                                               aria-label="Edit post: {{ $post->title }}">
                                                <i class="fas fa-edit" aria-hidden="true"></i>
                                            </a>
                                            <form action="{{ route('posts.destroy', $post) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-delete btn-sm" onclick="return confirm('Are you sure?')"
                                                        aria-label="Delete post: {{ $post->title }}">
                                                    <i class="fas fa-trash" aria-hidden="true"></i>
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                @endauth
                            </div>
                        </div>

                        {{-- Image --}}
                        @if($post->image)
                            <x-post-image :post="$post"
                                            class="card-img-bottom"
                                            style="max-height:260px; object-fit:cover;"
                                            :showPlaceholder="false" />
                        @endif
                    </div>
                @endforeach

                <!-- Пагинация -->
                <div class="d-flex justify-content-center">
                    {{ $posts->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <h4 class="text-muted">No posts yet</h4>
                    <p class="text-muted">Be the first to write one!</p>
                    <a href="{{ route('posts.create') }}" class="btn btn-create">
                        <i class="fas fa-plus me-2"></i>Create the first post
                    </a>
                </div>
            @endif
        </div>

        <!-- Боковая панель с фильтрами -->
        <div class="col-lg-4">
            <div class="sidebar-filter">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Filters</h5>
                        @if(request()->hasAny(['search','category','tag']))
                            <a href="{{ route('posts.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                        @endif
                    </div>
                    <div class="card-body">
                        <form action="{{ route('posts.index') }}" method="GET">
                            <!-- Поиск -->
                            <div class="mb-3">
                                <label class="form-label" for="search">Search by title</label>
                                <input type="text" name="search" id="search" class="form-control" value="{{ request('search') }}" placeholder="Type here...">
                            </div>

                            <!-- Фильтр по категориям -->
                            @if($categories->count() > 0)
                                <fieldset class="mb-3">
                                    <legend class="form-label fs-6">Categories</legend>
                                    @foreach($categories as $category)
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="category" id="category_{{ $category->id }}" value="{{ $category->id }}" {{ request('category') == $category->id ? 'checked' : '' }}>
                                            <label class="form-check-label" for="category_{{ $category->id }}">
                                                {{ $category->name }}
                                            </label>
                                        </div>
                                    @endforeach
                                </fieldset>
                            @endif

                            <!-- Фильтр по тегам -->
                            @if($tags->count() > 0)
                                <fieldset class="mb-3">
                                    <legend class="form-label fs-6">Tags</legend>
                                    @foreach($tags as $tag)
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="tag" id="tag_{{ $tag->id }}" value="{{ $tag->id }}" {{ request('tag') == $tag->id ? 'checked' : '' }}>
                                            <label class="form-check-label" for="tag_{{ $tag->id }}">
                                                {{ $tag->name }}
                                            </label>
                                        </div>
                                    @endforeach
                                </fieldset>
                            @endif

                            <!-- Кнопка поиска -->
                            <button type="submit" class="btn btn-primary w-100">Search</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
