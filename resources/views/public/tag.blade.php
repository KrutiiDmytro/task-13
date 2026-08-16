@extends('layouts.app')

@section('title', 'Tag: ' . $tag->name)

@section('content')
    <div class="app-container page">
        <div class="layout-with-sidebar">
            <div>
                <div class="page-head">
                    <span class="page-head__eyebrow">Tag</span>
                    <h1 class="page-head__title">#{{ $tag->name }}</h1>
                    <p class="page-head__sub">
                        {{ $posts->total() }} {{ \Illuminate\Support\Str::plural('article', $posts->total()) }} tagged
                        “{{ $tag->name }}”.
                    </p>
                </div>

                @if($posts->count() > 0)
                    <div class="post-grid">
                        @foreach($posts as $post)
                            <x-post-card :post="$post" />
                        @endforeach
                    </div>

                    <div class="d-flex justify-content-center mt-5">
                        {{ $posts->links() }}
                    </div>
                @else
                    <div class="empty-state">
                        <div class="empty-state__icon"><i class="fas fa-ghost" aria-hidden="true"></i></div>
                        <h2>Nothing tagged “{{ $tag->name }}” yet</h2>
                        <p>Try browsing another tag or category.</p>
                    </div>
                @endif
            </div>

            @include('partials.sidebar')
        </div>
    </div>
@endsection
