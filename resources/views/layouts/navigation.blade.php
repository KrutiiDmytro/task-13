@php
    // Provided by the view composer in AppServiceProvider; fall back to an
    // empty collection so the header still renders if it is ever missing.
    $navCategories = $navCategories ?? collect();
    $activeCategory = request()->routeIs('public.category') ? request()->route('slug') : null;
@endphp

<header class="site-header" id="siteHeader">
    <div class="app-container site-header__inner">

        {{-- Logo --}}
        <a class="brand" href="{{ route('home') }}">
            <span class="brand__mark" aria-hidden="true"><i class="fas fa-gamepad"></i></span>
            Pixel<span class="brand__accent">Pulse</span>
        </a>

        {{-- Category navigation --}}
        <nav class="site-nav-wrap" aria-label="Categories">
            <ul class="site-nav" id="siteNav">
                <li>
                    <a class="site-nav__link {{ request()->routeIs('home') ? 'is-active' : '' }}"
                       href="{{ route('home') }}">All</a>
                </li>
                @foreach($navCategories as $category)
                    <li>
                        <a class="site-nav__link {{ $activeCategory === $category->slug ? 'is-active' : '' }}"
                           href="{{ route('public.category', $category->slug) }}">{{ $category->name }}</a>
                    </li>
                @endforeach
            </ul>
        </nav>

        {{-- Site search --}}
        <form class="header-search" role="search" action="{{ route('public.search') }}" method="GET">
            <i class="fas fa-search header-search__icon" aria-hidden="true"></i>
            <label class="visually-hidden" for="headerSearch">Search articles</label>
            <input class="header-search__input"
                   type="search"
                   id="headerSearch"
                   name="q"
                   placeholder="Search articles…"
                   value="{{ request('q') }}">
        </form>

        <div class="header-actions">
            {{-- Light / dark switch --}}
            <button class="theme-toggle" type="button" id="themeToggle" aria-label="Toggle colour theme">
                <i class="fas fa-moon icon-moon" aria-hidden="true"></i>
                <i class="fas fa-sun icon-sun" aria-hidden="true"></i>
            </button>

            @auth
                <a class="btn-accent d-none d-sm-inline-flex" href="{{ route('posts.create') }}">
                    <i class="fas fa-plus" aria-hidden="true"></i> Write
                </a>

                <div class="dropdown">
                    <button class="btn-ghost dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        {{ Auth::user()->name }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profile</a></li>
                        @if(Auth::user()->admin)
                            <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}">Admin panel</a></li>
                        @endif
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">Log out</button>
                            </form>
                        </li>
                    </ul>
                </div>
            @endauth

            @guest
                <a class="btn-ghost d-none d-sm-inline-flex" href="{{ route('login') }}">Log in</a>
                <a class="btn-accent" href="{{ route('register') }}">Sign up</a>
            @endguest

            {{-- Mobile menu trigger --}}
            <button class="nav-burger" type="button" id="navBurger"
                    aria-label="Toggle menu" aria-expanded="false" aria-controls="siteNav">
                <i class="fas fa-bars" aria-hidden="true"></i>
            </button>
        </div>
    </div>
</header>
