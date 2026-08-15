@extends('adminlte::page')

@section('title', 'Панель управления')

@section('content_header')
    <h1>Панель управления</h1>
@stop

@section('content')
    <!-- Статистические карточки -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['posts_count'] }}</h3>
                    <p>Постов</p>
                </div>
                <div class="icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <a href="{{ route('admin.posts.index') }}" class="small-box-footer">
                    Подробнее <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['categories_count'] }}</h3>
                    <p>Категорий</p>
                </div>
                <div class="icon">
                    <i class="fas fa-tags"></i>
                </div>
                <a href="{{ route('admin.categories.index') }}" class="small-box-footer">
                    Подробнее <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['comments_count'] }}</h3>
                    <p>Комментариев</p>
                </div>
                <div class="icon">
                    <i class="fas fa-comments"></i>
                </div>
                <a href="{{ route('admin.comments.index') }}" class="small-box-footer">
                    Подробнее <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

                <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $stats['users_count'] }}</h3>
                    <p>Пользователей</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <a href="{{ route('admin.users.index') }}" class="small-box-footer">
                    Подробнее <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Недавние посты и комментарии -->
    <div class="row">
        <!-- Недавние посты -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Недавние посты</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Заголовок</th>
                                <th>Автор</th>
                                <th>Дата</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentPosts as $post)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.posts.show', $post) }}">
                                            {{ \Illuminate\Support\Str::limit($post->title, 30) }}
                                        </a>
                                    </td>
                                    <td>{{ $post->user->name ?? 'Неизвестно' }}</td>
                                    <td>{{ $post->created_at->format('d.m.Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">Нет постов</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Недавние комментарии -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Недавние комментарии</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Комментарий</th>
                                <th>Пост</th>
                                <th>Дата</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentComments as $comment)
                                <tr>
                                    <td>{{ \Illuminate\Support\Str::limit($comment->content, 30) }}</td>
                                    <td>
                                        <a href="{{ route('admin.posts.show', $comment->post) }}">
                                        {{ \Illuminate\Support\Str::limit($comment->post->title, 20) }}                                        </a>
                                    </td>
                                    <td>{{ $comment->created_at->format('d.m.Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">Нет комментариев</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
@stop

@section('js')
    <script>
        console.log('Admin Dashboard loaded');
    </script>
@stop
