<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'PixelPulse') }}</title>

        {{-- Same pre-paint theme bootstrap as the main layout --}}
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

        @vite(['resources/css/app.css', 'resources/css/guest.css', 'resources/js/app.js'])

        @stack('styles')
    </head>

    <body>
        <div class="auth-shell">
            <div class="auth-card">
                <a class="auth-brand" href="{{ route('home') }}">
                    <span class="brand__mark" aria-hidden="true"><i class="fas fa-gamepad"></i></span>
                    Pixel<span class="brand__accent">Pulse</span>
                </a>

                {{ $slot }}
            </div>
        </div>

        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

        @stack('scripts')
    </body>
</html>
