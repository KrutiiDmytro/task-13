@extends('adminlte::page')

@section('title', 'New post')

@section('content_header')
    <h1>Create a new post</h1>
@stop

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Post creation form</h3>
        </div>

        <form action="{{ route('admin.posts.store') }}" method="POST">
            @csrf

            <div class="card-body">
                {{-- Title --}}
                <div class="form-group">
                    <label for="title">Title</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-heading"></i></span>
                        </div>
                        <input type="text" class="form-control @error('title') is-invalid @enderror"
                               id="title" name="title" placeholder="Enter the post title"
                               value="{{ old('title') }}" required>
                        @error('title')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- Content --}}
                <div class="form-group">
                    <label for="content">Content</label>
                    <textarea class="form-control @error('content') is-invalid @enderror"
                              id="content" name="content" rows="10"
                              placeholder="Enter the post content" required>{{ old('content') }}</textarea>
                    @error('content')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Category --}}
                <div class="form-group">
                    <label for="category_id">Category</label>
                    <select class="form-control @error('category_id') is-invalid @enderror"
                            id="category_id" name="category_id">
                        <option value="">-- Choose a category --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}"
                                    @selected(old('category_id') == $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Author --}}
                <div class="form-group">
                    <label for="user_id">Author (registered user, optional)</label>
                        <select class="form-control @error('user_id') is-invalid @enderror"
                                id="user_id" name="user_id">
                        <option value="">— Leave empty (you become the author) —</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" @selected(old('user_id') == $user->id)>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                        </select>
                        @error('user_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">
                            If you do not pick a user, you become the author. Otherwise fill in the guest author fields below.
                        </small>
                </div>

<div class="form-group">
    <label for="author_name">Author (guest name)</label>
    <input type="text" class="form-control @error('author_name') is-invalid @enderror"
           id="author_name" name="author_name" value="{{ old('author_name') }}"
           placeholder="Guest author name">
    @error('author_name')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label for="author_email">Guest author email</label>
    <input type="email" class="form-control @error('author_email') is-invalid @enderror"
           id="author_email" name="author_email" value="{{ old('author_email') }}"
           placeholder="email@example.com">
    @error('author_email')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>

                {{-- Tags --}}
                <div class="form-group">
                    <label for="tags">Tags</label>
                    <select class="form-control @error('tags') is-invalid @enderror"
                            id="tags" name="tags[]" multiple>
                        @foreach($tags as $tag)
                            <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted">Pick existing tags or type new ones</small>
                    @error('tags')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> New post
                </button>
                <a href="{{ route('admin.posts.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
@stop

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-theme@0.1.0-beta.10/dist/select2-bootstrap.min.css" rel="stylesheet" />
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#tags').select2({
                tags: true,
                tokenSeparators: [','],
                placeholder: 'Pick or type tags',
                theme: 'bootstrap'
            });
        });
    </script>
@stop
