@extends('adminlte::page')

@section('title', 'Управление категориями')

@section('content_header')
    <h1>Управление категориями</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Список категорий</h3>
            <div class="card-tools">
                <a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Создать категорию
                </a>
            </div>
        </div>
        <div class="card-body">
            @if($categories->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Название</th>
                                <th>Количество постов</th>
                                <th>Дата создания</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categories as $category)
                                <tr>
                                    <td>{{ $category->id }}</td>
                                    <td>
                                        <strong>{{ $category->name }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge badge-primary">{{ $category->posts_count ?? 0 }}</span>
                                    </td>
                                    <td>{{ $category->created_at->format('d.m.Y H:i') }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.categories.show', $category) }}"
                                               class="btn btn-info btn-sm" title="Просмотр">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.categories.edit', $category) }}"
                                               class="btn btn-warning btn-sm" title="Редактировать">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if(($category->posts_count ?? 0) == 0)
                                                <form action="{{ route('admin.categories.destroy', $category) }}"
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('Вы уверены, что хотите удалить эту категорию?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" title="Удалить">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <button class="btn btn-danger btn-sm" disabled
                                                        title="Нельзя удалить категорию с постами">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Пагинация (если есть) -->
                @if(method_exists($categories, 'links'))
                    <div class="d-flex justify-content-center">
                        {{ $categories->links() }}
                    </div>
                @endif
            @else
                <div class="text-center">
                    <div class="alert alert-info">
                        <h4><i class="fas fa-info-circle"></i> Категорий пока нет</h4>
                        <p>Создайте первую категорию для группировки постов.</p>
                        <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Создать первую категорию
                        </a>
                    </div>
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
        console.log('Categories index loaded');
    </script>
@stop
