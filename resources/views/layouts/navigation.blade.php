<nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
    <div class="container-fluid">

        {{-- Логотип --}}
        <a class="navbar-brand fw-semibold text-dark" href="{{ route('home') }}">
            <x-application-logo class="d-inline-block"/>
        </a>

        {{-- Бургер для мобильных --}}
        <button class="navbar-toggler border-0"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#mainNav"
                aria-controls="mainNav"
                aria-expanded="false"
                aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        {{-- Ссылки, поиск и меню пользователя --}}
        <div class="collapse navbar-collapse" id="mainNav">
            {{-- Поиск тегов (слева) --}}
            <form class="d-flex me-auto my-2 my-lg-0" action="{{ route('tags.index') }}" method="GET">
                <input class="form-control me-2" type="search" name="q" placeholder="Пошук тегів..." aria-label="Пошук тегів" value="{{ request('q') }}">
                <button class="btn btn-outline-success" type="submit">Пошук</button>
            </form>

            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                {{-- Главная --}}
                <li class="nav-item">
                    <a class="nav-link text-dark" href="{{ route('home') }}">Главная</a>
                </li>

                {{-- Кнопка «Создать пост» --}}
                <li class="nav-item">
                    <a class="btn btn-light btn-sm fw-semibold" href="{{ route('posts.create') }}">
                        <i class="fas fa-plus me-1"></i> Создать пост
                    </a>
                </li>

                {{-- Авторизован / выпадающее меню --}}
                @auth
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-dark"
                           href="#" role="button"
                           data-bs-toggle="dropdown"
                           aria-expanded="false">
                            {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="{{ route('profile.edit') }}">Профиль</a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">Выйти</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @endauth

                {{-- Гость --}}
                @guest
                    <li class="nav-item">
                        <a class="nav-link text-dark" href="{{ route('login') }}">Вход</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-dark" href="{{ route('register') }}">Регистрация</a>
                    </li>
                @endguest
            </ul>
        </div>
    </div>
</nav>
