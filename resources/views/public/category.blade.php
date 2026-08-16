@extends('layouts.app')

@section('title', $category->name)

@section('content')
    <div class="app-container page">
        <div class="layout-with-sidebar">
            <div>
                <div class="page-head">
                    <span class="page-head__eyebrow">Category</span>
                    <h1 class="page-head__title">{{ $category->name }}</h1>

                    @if($category->description)
                        <p class="page-head__sub">{{ $category->description }}</p>
                    @endif
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
                        <h2>No articles in this category yet</h2>
                        <p>Check back soon — we are working on it.</p>
                    </div>
                @endif
            </div>

            @include('partials.sidebar')
        </div>
    </div>
@endsection
