@extends('adminlte::page')

@section('title', 'Comment management')

@section('content_header')
    <h1>Comment management</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Comments</h3>
            <div class="card-tools">
                <a href="{{ route('admin.comments.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Create comment
                </a>
            </div>
        </div>
        <div class="card-body">
            @if($comments->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Content</th>
                                <th>Post</th>
                                <th>Author</th>
                                <th>Email</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($comments as $comment)
                                <tr>
                                    <td>{{ $comment->id }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($comment->content, 50) }}</td>
                                    <td>
                                        @if($comment->post)
                                            <a href="{{ route('admin.posts.show', $comment->post) }}"
                                               class="text-primary">
                                                {{ \Illuminate\Support\Str::limit($comment->post->title, 30) }}
                                            </a>
                                        @else
                                            <span class="text-muted">Post deleted</span>
                                        @endif
                                    </td>
                                    <td>{{ $comment->author_name ?: 'Anonymous' }}</td>
                                    <td>{{ $comment->author_email ?: 'Not specified' }}</td>
                                    <td>{{ $comment->created_at->format('d.m.Y H:i') }}</td>
                                    <td>
                                        <div class="btn-group" role="group" aria-label="Actions for comment #{{ $comment->id }}">
                                            <a href="{{ route('admin.comments.show', $comment) }}"
                                               class="btn btn-info btn-sm" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.comments.edit', $comment) }}"
                                               class="btn btn-warning btn-sm" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.comments.destroy', $comment) }}"
                                                  method="POST" class="d-inline"
                                                  onsubmit="return confirm('Are you sure you want to delete this comment?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Пагинация -->
                <div class="d-flex justify-content-center">
                    {{ $comments->links() }}
                </div>
            @else
                <div class="text-center">
                    <p>No comments yet.</p>
                    <a href="{{ route('admin.comments.create') }}" class="btn btn-primary">
                        Create the first comment
                    </a>
                </div>
            @endif
        </div>
    </div>
@stop

@section('css')
    <style>
        .btn-group .btn {
            margin-right: 2px;
        }
        .table td {
            vertical-align: middle;
        }
    </style>
@stop

@section('js')
    <script>
        // Автообновление таблицы каждые 30 секунд
        setTimeout(() => {
            location.reload();
        }, 30000);
    </script>
@stop
