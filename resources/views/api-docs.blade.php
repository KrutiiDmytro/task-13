<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog API Documentation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 0;
        }
        .feature-card {
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        .feature-card:hover {
            transform: translateY(-5px);
        }
        .endpoint-badge {
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="hero-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h1 class="display-4 fw-bold mb-4">Blog API Documentation</h1>
                    <p class="lead mb-4">
                        Полная REST API для системы управления блогом с поддержкой JSON и XML форматов
                    </p>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <a href="{{ url('/api/documentation') }}" class="btn btn-light btn-lg px-4 me-md-2">
                            📚 Открыть Swagger UI
                        </a>
                        <a href="{{ url('/api-info') }}" class="btn btn-outline-light btn-lg px-4">
                            ℹ️ API Info
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container my-5">
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card feature-card h-100">
                    <div class="card-body">
                        <h5 class="card-title">
                            📝 Posts API
                        </h5>
                        <p class="card-text">
                            Управление постами блога: создание, чтение, обновление и удаление.
                        </p>
                        <div class="mb-3">
                            <span class="badge bg-success endpoint-badge">GET /api/v1/posts</span><br>
                            <span class="badge bg-primary endpoint-badge">POST /api/v1/posts</span><br>
                            <span class="badge bg-info endpoint-badge">PUT /api/v1/posts/{id}</span><br>
                            <span class="badge bg-danger endpoint-badge">DELETE /api/v1/posts/{id}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-4">
                <div class="card feature-card h-100">
                    <div class="card-body">
                        <h5 class="card-title">
                            📁 Categories API
                        </h5>
                        <p class="card-text">
                            Управление категориями постов с подсчетом количества постов.
                        </p>
                        <div class="mb-3">
                            <span class="badge bg-success endpoint-badge">GET /api/v1/categories</span><br>
                            <span class="badge bg-primary endpoint-badge">POST /api/v1/categories</span><br>
                            <span class="badge bg-info endpoint-badge">PUT /api/v1/categories/{id}</span><br>
                            <span class="badge bg-danger endpoint-badge">DELETE /api/v1/categories/{id}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-lg-8 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            🚀 Быстрый старт
                        </h5>
                    </div>
                    <div class="card-body">
                        <h6>1. Получение списка постов (JSON):</h6>
                        <pre class="bg-light p-3 rounded"><code>curl -X GET "{{ url('/api/v1/posts?format=json') }}" \
     -H "Accept: application/json"</code></pre>

                        <h6>2. Получение списка постов (XML):</h6>
                        <pre class="bg-light p-3 rounded"><code>curl -X GET "{{ url('/api/v1/posts?format=xml') }}" \
     -H "Accept: application/xml"</code></pre>

                        <h6>3. Создание поста (с аутентификацией):</h6>
                        <pre class="bg-light p-3 rounded"><code>curl -X POST "{{ url('/api/v1/posts') }}" \
     -H "Authorization: Bearer YOUR_TOKEN" \
     -H "Content-Type: application/json" \
     -d '{
       "title": "Новый пост",
       "content": "Содержание поста",
       "category_id": 1
     }'</code></pre>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-lg-12">
                <div class="text-center">
                    <h3>Полезные ссылки</h3>
                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        <a href="{{ url('/api/documentation') }}" class="btn btn-primary">
                            📚 Swagger UI
                        </a>
                        <a href="{{ url('/api-info') }}" class="btn btn-outline-primary">
                            ℹ️ API Information
                        </a>
                        <a href="{{ url('/api/v1/posts') }}" class="btn btn-outline-success">
                            ▶️ Try Posts API
                        </a>
                        <a href="{{ url('/api/v1/categories') }}" class="btn btn-outline-warning">
                            ▶️ Try Categories API
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p>&copy; 2023 Blog API. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p>Powered by Laravel & Swagger UI</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
