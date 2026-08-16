{{-- Список тегів із пошуком --}}
@extends('layouts.app')

@section('title', 'Tags')

@section('content')
<div class="container py-4">
    <h1 class="h3 mb-3">Tags</h1>

    {{--  Search tags (перейде на /tags?q=...) --}}
    <form action="{{ route('tags.index') }}" method="GET" class="mb-3">
        <div class="input-group">
            <input type="text"
                   name="q"
                   value="{{ request('q') }}"
                   class="form-control"
                   placeholder="Search tags..."
                   aria-label="Search tags">
            <button class="btn btn-outline-secondary" type="submit">Search</button>
        </div>
    </form>

    {{--  Якщо є знайдені теги — показуємо як бейджі з посиланнями --}}
    @if(isset($tags) && $tags->count())
        <div class="d-flex flex-wrap gap-2">
            @foreach($tags as $tag)
                <a href="{{ route('tags.show', $tag->slug) }}"
                   class="badge bg-secondary text-decoration-none">
                    #{{ $tag->name }}
                </a>
            @endforeach
        </div>

        {{--  Пагінація результатів пошуку/списку тегів --}}
        @if(method_exists($tags, 'links'))
            <div class="mt-3">
                {{ $tags->links() }}
            </div>
        @endif
    @else
        <div class="alert alert-info mt-3">
            {{--  Повідомлення коли тегів немає або пошук нічого не дав --}}
            No tags found.
        </div>
    @endif
</div>
@endsection
