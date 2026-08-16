<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@hasSection('title')@yield('title') — {{ config('app.name', 'PixelPulse') }}@else{{ config('app.name', 'PixelPulse') }}@endif</title>

        {{-- Apply the stored theme before first paint so the page never flashes the wrong palette --}}
        <script>
            (function () {
                var stored = null;
                try { stored = localStorage.getItem('theme'); } catch (e) { /* private mode */ }
                var theme = stored
                    || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
                document.documentElement.setAttribute('data-theme', theme);
                document.documentElement.setAttribute('data-bs-theme', theme);
            })();
        </script>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">

        <!-- Bootstrap CSS (base layer) -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

        <!-- Font Awesome -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

        {{-- Theme layer — must load after Bootstrap so our tokens win --}}
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @stack('styles')
    </head>

    <body>
        <a class="skip-link" href="#main">Skip to content</a>

        @include('layouts.navigation')

        @isset($header)
            <header class="app-container" style="padding-block: 32px 0;">
                {{ $header }}
            </header>
        @endisset

        <main id="main">
            @yield('content')

            {{-- Supports the <x-app-layout> component syntax used by the dashboard --}}
            {{ $slot ?? '' }}
        </main>

        @include('partials.footer')

        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

        @stack('scripts')
    </body>
</html>
