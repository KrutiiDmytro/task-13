<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Наш Блог') - Laravel</title>
    <!-- Подключаем Bootstrap для стилей -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="{{ route('posts.index') }}">Блог на Laravel</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                {{-- Ссылки, выровненные по левому краю --}}
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('posts.index') ? 'active' : '' }}" href="{{ route('posts.index') }}">Все посты</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('tags.index') ? 'active' : '' }}" href="{{ route('tags.index') }}">Теги</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('comments.index') ? 'active' : '' }}" href="{{ route('comments.index') }}">Комментарии</a>
                    </li>
                </ul>
                {{-- Ссылки, выровненные по правому краю --}}
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="btn btn-primary" href="{{ route('posts.create') }}">Создать пост</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container">
        @if(session('success'))
            <div class="alert alert-success" role="alert">
                {{ session('success') }}
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="container text-center text-muted py-4 mt-4 border-top">
        Блог на Laravel &copy; {{ date('Y') }}
    </footer>

    {{-- JS для работы компонентов Bootstrap --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    {{-- Место для подключения дополнительных скриптов со страниц --}}
    @stack('scripts')
</body>
</html>