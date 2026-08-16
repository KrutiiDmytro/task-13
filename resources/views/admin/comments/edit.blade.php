@extends('adminlte::page')

@section('title', 'Edit comment')

@section('content_header')
    <h1>Edit comment #{{ $comment->id }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Editing comment</h3>
            <div class="card-tools">
                <a href="{{ route('admin.comments.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Back to list
                </a>
            </div>
        </div>

        <form action="{{ route('admin.comments.update', $comment) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-6">
                        <!-- Выбор поста -->
                        <div class="form-group">
                            <label for="post_id">Post <span class="text-danger">*</span></label>
                            <select name="post_id" id="post_id" class="form-control @error('post_id') is-invalid @enderror" required>
                                <option value="">Choose a post...</option>
                                @foreach($posts as $post)
                                    <option value="{{ $post->id }}" {{ (old('post_id', $comment->post_id) == $post->id) ? 'selected' : '' }}>
                                        {{ $post->title }}
                                    </option>
                                @endforeach
                            </select>
                            @error('post_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <!-- Author name -->
                        <div class="form-group">
                            <label for="author">Author name</label>
                            <input type="text"
                                   name="author"
                                   id="author"
                                   class="form-control @error('author') is-invalid @enderror"
                                   value="{{ old('name', $comment->name) }}"
                                   placeholder="Name (optional)">
                            @error('author')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <!-- Author email -->
                        <div class="form-group">
                            <label for="email">Author email</label>
                            <input type="email"
                                   name="email"
                                   id="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $comment->email) }}"
                                   placeholder="email@example.com">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Comment content -->
                <div class="form-group">
                    <label for="content">Comment content <span class="text-danger">*</span></label>
                    <textarea name="content"
                              id="content"
                              class="form-control @error('content') is-invalid @enderror"
                              rows="6"
                              required
                              placeholder="Enter the comment text...">{{ old('content', $comment->content) }}</textarea>
                    @error('content')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save changes
                </button>
                <a href="{{ route('admin.comments.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
@stop
