@extends('layouts.app')

@section('title', 'View comment')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>View comment</h1>
        <div>
            <a href="{{ route('comments.edit', $comment) }}" class="btn btn-warning">Edit</a>
            <a href="{{ route('comments.index') }}" class="btn btn-secondary">Back to list</a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Comment #{{ $comment->id }}</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Author:</strong> {{ $comment->author_name ?? 'Not specified' }}</p>
                    <p><strong>Email:</strong> {{ $comment->author_email ?? 'Not specified' }}</p>
                    <p><strong>Created:</strong> {{ $comment->created_at->format('d.m.Y H:i') }}</p>
                    <p><strong>Updated:</strong> {{ $comment->updated_at->format('d.m.Y H:i') }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Post:</strong>
                        <a href="{{ route('posts.show', $comment->post) }}" class="text-decoration-none">
                            {{ $comment->post->title }}
                        </a>
                    </p>
                </div>
            </div>

            <hr>

            <div class="mt-3">
                <h6><strong>Comment content:</strong></h6>
                <div class="bg-light p-3 rounded">
                    {{ $comment->content }}
                </div>
            </div>
        </div>
        <div class="card-footer">
            <form action="{{ route('comments.destroy', $comment) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this comment?')">
                    Delete comment
                </button>
            </form>
        </div>
    </div>
@endsection
