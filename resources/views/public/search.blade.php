@extends('layouts.app')

@section('title', $q ? 'Search: ' . $q : 'Search')

@section('content')
    <div class="app-container page">
        <div class="layout-with-sidebar">
            <div>
                <div class="page-head">
                    <span class="page-head__eyebrow">Search</span>
                    <h1 class="page-head__title">
                        @if($q)
                            Results for “{{ $q }}”
                        @else
                            Search the blog
                        @endif
                    </h1>

                    @if($q)
                        <p class="page-head__sub">
                            {{ $posts->total() }} {{ \Illuminate\Support\Str::plural('article', $posts->total()) }} found.
                        </p>
                    @endif
                </div>

                <form class="search-form" method="GET" action="{{ route('public.search') }}">
                    <label class="visually-hidden" for="searchQuery">Search query</label>
                    <input type="search"
                           class="form-control"
                           id="searchQuery"
                           name="q"
                           placeholder="Type a game, a guide, a keyword…"
                           value="{{ $q }}">
                    <button class="btn-accent" type="submit">Search</button>
                </form>

                @if($posts->count() > 0)
                    <div class="post-grid">
                        @foreach($posts as $post)
                            <x-post-card :post="$post" />
                        @endforeach
                    </div>

                    <div class="d-flex justify-content-center mt-5">
                        {{ $posts->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="empty-state">
                        <div class="empty-state__icon"><i class="fas fa-magnifying-glass" aria-hidden="true"></i></div>
                        @if($q)
                            <h2>No matches for “{{ $q }}”</h2>
                            <p>Try a different keyword or browse the categories.</p>
                        @else
                            <h2>What are you looking for?</h2>
                            <p>Enter a keyword above to search across every article.</p>
                        @endif
                    </div>
                @endif
            </div>

            @include('partials.sidebar')
        </div>
    </div>
@endsection
