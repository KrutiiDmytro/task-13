@extends('adminlte::page')

@section('title', 'View user')

@section('content_header')
    <h1>User: {{ $user->name }}</h1>
@stop

@section('content')
    <div class="row">
        <!-- User details -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">User details</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to list
                        </a>
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">ID:</dt>
                        <dd class="col-sm-8">{{ $user->id }}</dd>

                        <dt class="col-sm-4">Name:</dt>
                        <dd class="col-sm-8"><strong>{{ $user->name }}</strong></dd>

                        <dt class="col-sm-4">Email:</dt>
                        <dd class="col-sm-8">{{ $user->email }}</dd>

                        <dt class="col-sm-4">Email verified:</dt>
                        <dd class="col-sm-8">
                            @if($user->email_verified_at)
                                <span class="badge badge-success">Yes</span>
                                <small class="text-muted">({{ $user->email_verified_at->format('d.m.Y H:i') }})</small>
                            @else
                                <span class="badge badge-warning">No</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4">Roles:</dt>
                        <dd class="col-sm-8">
                            @if($user->roles->count() > 0)
                                @foreach($user->roles as $role)
                                    <span class="badge badge-info">{{ ucfirst($role->name) }}</span>
                                @endforeach
                            @else
                                <span class="badge badge-secondary">No roles</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4">Registered:</dt>
                        <dd class="col-sm-8">{{ $user->created_at->format('d.m.Y H:i:s') }}</dd>

                        <dt class="col-sm-4">Last updated:</dt>
                        <dd class="col-sm-8">{{ $user->updated_at->format('d.m.Y H:i:s') }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Statistics and actions -->
        <div class="col-md-6">
            <!-- Statistics -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Statistics</h3>
                </div>
                <div class="card-body">
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="fas fa-newspaper"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Posts</span>
                            <span class="info-box-number">{{ $user->posts->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Actions</h3>
                </div>
                <div class="card-body">
                    <div class="btn-group-vertical w-100">
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning mb-2">
                            <i class="fas fa-edit"></i> Edit user
                        </a>
                        @if($user->posts->count() > 0)
                            <a href="{{ route('admin.posts.index', ['user_id' => $user->id]) }}" class="btn btn-info mb-2">
                                <i class="fas fa-list"></i> Posts by this user
                            </a>
                        @endif
                        @if($user->id !== auth()->id())
                            @if($user->posts->count() == 0)
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Are you sure you want to delete this user?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger w-100">
                                        <i class="fas fa-trash"></i> Delete user
                                    </button>
                                </form>
                            @else
                                <button class="btn btn-danger w-100" disabled title="Cannot delete a user that still has posts">
                                    <i class="fas fa-trash"></i> Delete user
                                </button>
                                <small class="text-muted mt-2">
                                    Cannot delete: this user still has posts
                                </small>
                            @endif
                        @else
                            <button class="btn btn-danger w-100" disabled title="You cannot delete yourself">
                                <i class="fas fa-trash"></i> Delete user
                            </button>
                            <small class="text-muted mt-2">
                                You cannot delete your own account
                            </small>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Posts by this user -->
    @if($user->posts->count() > 0)
    <div class="card mt-4">
        <div class="card-header">
            <h3 class="card-title">Posts by this user ({{ $user->posts->count() }})</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($user->posts->take(10) as $post)
                        <tr>
                            <td>
                                <a href="{{ route('admin.posts.show', $post) }}">
                                    {{ \Illuminate\Support\Str::limit($post->title, 50) }}
                                </a>
                            </td>
                            <td>
                                @if($post->category)
                                    <span class="badge badge-info">{{ $post->category->name }}</span>
                                @else
                                    <span class="badge badge-secondary">No category</span>
                                @endif
                            </td>
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
            @if($user->posts->count() > 10)
                <div class="text-center">
                    <a href="{{ route('admin.posts.index', ['user_id' => $user->id]) }}" class="btn btn-primary">
                        Show all posts by this user
                    </a>
                </div>
            @endif
        </div>
    </div>
    @endif
@stop
