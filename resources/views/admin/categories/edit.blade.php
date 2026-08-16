@extends('adminlte::page')

@section('title', 'Edit category')

@section('content_header')
    <h1>Edit category</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Editing category: {{ $category->name }}</h3>
            <div class="card-tools">
                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Back to list
                </a>
                <a href="{{ route('admin.categories.show', $category) }}" class="btn btn-info btn-sm">
                    <i class="fas fa-eye"></i> View
                </a>
            </div>
        </div>

        <form action="{{ route('admin.categories.update', $category) }}" method="POST">
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

                <!-- Category name -->
                <div class="form-group">
                    <label for="name">Category name <span class="text-danger">*</span></label>
                    <input type="text"
                           name="name"
                           id="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $category->name) }}"
                           required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Statistics -->
                <div class="alert alert-info">
                    <strong>Statistics:</strong> This category contains {{ $category->posts->count() }} posts.
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save changes
                </button>
                <a href="{{ route('admin.categories.show', $category) }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
@stop
