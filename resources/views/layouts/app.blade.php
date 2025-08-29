<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Bootstrap CSS -->
         <link rel="stylesheet" href="{{ asset('css/app.css') }}">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- Font Awesome -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

        <!-- Кастомные стили -->
        <style>
            /* Карточка поста */
            .post-card {
                border: none;
                border-radius: 0.75rem;
                box-shadow: 0 4px 18px rgba(0, 0, 0, 0.06);
                transition: transform 0.18s ease, box-shadow 0.18s ease;
            }
            
            .post-card:hover {
                transform: translateY(-4px);
                box-shadow: 0 8px 22px rgba(0, 0, 0, 0.08);
            }
            
            /* Кнопки */
            .btn-create {
                font-weight: 600;
                border-radius: 50rem;
                padding-left: 1.5rem;
                padding-right: 1.5rem;
                background-color: #ff5722;
                border-color: #ff5722;
                color: white;
            }
            
            .btn-create:hover {
                background-color: #e64a19;
                border-color: #e64a19;
            }
            
            .btn-icon {
                width: 34px;
                height: 34px;
                padding: 0;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-size: 0.875rem;
            }
            
            .btn-edit {
                background-color: #ffc107;
                border-color: #ffc107;
                color: #000;
            }
            
            .btn-edit:hover {
                background-color: #ffb300;
                border-color: #ffb300;
            }
            
            .btn-delete {
                background-color: #dc3545;
                border-color: #dc3545;
                color: white;
            }
            
            .btn-delete:hover {
                background-color: #c82333;
                border-color: #c82333;
            }
            
            /* Фильтры (sidebar) */
            .sidebar-filter .card {
                border: none;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
                border-radius: 0.75rem;
                position: sticky;
                top: 6rem;
            }
            
            /* Хедер */
            .header-bg {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            }
            
            /* Навбар логотип */
            .navbar-brand img,
            .navbar-brand svg {
                height: 32px;
                width: auto;
            }
            
            /* Общие стили */
            body {
                font-family: "Inter", "Helvetica Neue", Arial, sans-serif;
                background-color: #f5f7fa;
            }
            
            /* Кастомная цветовая схема */
            :root {
                --primary-color: #ff5722;
                --secondary-color: #607d8b;
                --bg-color: #f5f7fa;
            }
        </style>
        
        @stack('styles')
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                @yield('content')
            </main>
        </div>

        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        
        @stack('scripts')
    </body>
</html>