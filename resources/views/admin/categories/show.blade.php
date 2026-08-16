@extends('adminlte::page')

@section('title', 'View category')

@section('content_header')
    <h1>Category: {{ $category->name }}</h1>
@stop

@section('content')
    <div class="row">
        <!-- Category details -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Category details</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to list
                        </a>
                        <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">ID:</dt>
                        <dd class="col-sm-8">{{ $category->id }}</dd>

                        <dt class="col-sm-4">Name:</dt>
                        <dd class="col-sm-8"><strong>{{ $category->name }}</strong></dd>

                        <dt class="col-sm-4">Post count:</dt>
                        <dd class="col-sm-8">
                            <span class="badge badge-primary">{{ $category->posts->count() }}</span>
                        </dd>

                        <dt class="col-sm-4">Created:</dt>
                        <dd class="col-sm-8">{{ $category->created_at->format('d.m.Y H:i:s') }}</dd>

                        <dt class="col-sm-4">Updated:</dt>
                        <dd class="col-sm-8">{{ $category->updated_at->format('d.m.Y H:i:s') }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Actions</h3>
                </div>
                <div class="card-body">
                    <div class="btn-group-vertical w-100">
                        <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-warning mb-2">
                            <i class="fas fa-edit"></i> Edit category
                        </a>
                        <a href="{{ route('admin.posts.index', ['category_id' => $category->id]) }}" class="btn btn-info mb-2">
                            <i class="fas fa-list"></i> Posts in this category
                        </a>
                        @if($category->posts->count() == 0)
                            <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Are you sure you want to delete this category?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger w-100">
                                    <i class="fas fa-trash"></i> Delete category
                                </button>
                            </form>
                        @else
                            <button class="btn btn-danger w-100" disabled title="Cannot delete a category that still has posts">
                                <i class="fas fa-trash"></i> Delete category
                            </button>
                            <small class="text-muted mt-2">
                                Cannot delete: this category still has posts
                            </small>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Posts in this category -->
    @if($category->posts->count() > 0)
    <div class="card mt-4">
        <div class="card-header">
            <h3 class="card-title">Posts in this category ({{ $category->posts->count() }})</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($category->posts->take(10) as $post)
                        <tr>
                            <td>
                                <a href="{{ route('admin.posts.show', $post) }}">
                                {{ \Illuminate\Support\Str::limit($post->title, 50) }}                                </a>
                            </td>
                            <td>{{ $post->user->name ?? 'Unknown' }}</td>
                            <td>{{ $post->created_at->format('d.m.Y H:i') }}</td>
                            <td>
                                <a href="{{ route('admin.posts.show', $post) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.posts.edit', $post) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($category->posts->count() > 10)
                <div class="text-center">
                    <a href="{{ route('admin.posts.index', ['category_id' => $category->id]) }}" class="btn btn-primary">
                        Show all posts in this category
                    </a>
                </div>
            @endif
        </div>
    </div>
    @endif
@stop
