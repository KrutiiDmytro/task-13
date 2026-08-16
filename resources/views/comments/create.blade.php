@extends('layouts.app')

@section('title', 'Create comment')

@section('content')
    <h1>Create a new comment</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            {{-- форма создаёт комментарий --}}
            <form action="{{ route('comments.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="author" class="form-label">Author</label>
                    <input type="text"
                           id="author"
                           name="author"
                           class="form-control"
                           value="{{ old('author') }}"
                           required>
                </div>

                <div class="mb-3">
                    <label for="content" class="form-label">Comment</label>
                    <textarea id="content"
                              name="content"
                              class="form-control"
                              rows="4"
                              required>{{ old('content') }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="post_id" class="form-label">Which post?</label>
                    <select id="post_id" name="post_id" class="form-select" required>
                        <option value="">Choose a post</option>
                        @foreach($posts as $post)
                            <option value="{{ $post->id }}"
                                    @selected(old('post_id') == $post->id)>
                                {{ $post->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Create comment</button>
                <a href="{{ route('comments.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
@endsection
