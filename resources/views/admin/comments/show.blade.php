@extends('adminlte::page')

@section('title', 'View comment')

@section('content_header')
    <h1>View comment #{{ $comment->id }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Comment details</h3>
            <div class="card-tools">
                <a href="{{ route('admin.comments.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Back to list
                </a>
                <a href="{{ route('admin.comments.edit', $comment) }}" class="btn btn-warning btn-sm">
                    <i class="fas fa-edit"></i> Edit
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <strong>ID:</strong> {{ $comment->id }}<br>
                    <strong>Author:</strong> {{ $comment->name ?: 'Anonymous' }}<br>
                    <strong>Email:</strong> {{ $comment->email ?: 'Not specified' }}<br>
                    <strong>Created:</strong> {{ $comment->created_at->format('d.m.Y H:i:s') }}<br>
                </div>
                <div class="col-md-6">
                    <strong>Post:</strong>
                    @if($comment->post)
                        <a href="{{ route('admin.posts.show', $comment->post) }}">
                            {{ $comment->post->title }}
                        </a>
                    @else
                        <span class="text-muted">Post deleted</span>
                    @endif
                </div>
            </div>
            <hr>
            <div>
                <strong>Content:</strong>
                <div class="border p-3 mt-2">
                    {{ $comment->content }}
                </div>
            </div>
        </div>
    </div>
@stop
