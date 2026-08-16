@extends('layouts.app')

@section('title', 'Edit comment')

@section('content')
    <h1>Edit comment</h1>

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
            <form action="{{ route('comments.update', $comment) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label for="author" class="form-label">Author</label>
                    <input type="text" id="author" name="author" class="form-control" value="{{ old('author', $comment->author_name) }}" required>
                </div>

                <div class="mb-3">
                    <label for="content" class="form-label">Comment</label>
                    <textarea id="content" name="content" class="form-control" rows="4" required>{{ old('content', $comment->content) }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="post_id" class="form-label">Post</label>
                    <select id="post_id" name="post_id" class="form-control" required>
                        <option value="">Choose a post</option>
                        @foreach($posts as $post)
                            <option value="{{ $post->id }}" {{ old('post_id', $comment->post_id) == $post->id ? 'selected' : '' }}>
                                {{ $post->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Update comment</button>
                <a href="{{ route('comments.show', $comment) }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
@endsection
