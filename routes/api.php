<?php

use App\Http\Controllers\Api\V1\PostController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CommentController;
use App\Http\Controllers\Api\V1\TagController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Здесь можно зарегистрировать API маршруты для вашего приложения.
| Эти маршруты загружаются RouteServiceProvider и все они будут назначены
| middleware группе "api".
|
| Поддерживаемые форматы ответов:
| - JSON (по умолчанию): ?format=json или Accept: application/json
| - XML: ?format=xml или Accept: application/xml
|
*/

// Получение информации об аутентифицированном пользователе
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Корневой маршрут API - информация об API
Route::get('/', function () {
    return response()->json([
        'name' => 'Blog API',
        'version' => '1.0.0',
        'description' => 'REST API для системы управления блогом',
        'documentation' => url('/api/documentation'),
        'endpoints' => [
            'v1' => url('/api/v1'),
        ],
        'supported_formats' => ['json', 'xml'],
        'authentication' => 'Laravel Sanctum'
    ]);
});

/*
|--------------------------------------------------------------------------
| API V1 Routes
|--------------------------------------------------------------------------
|
| Все маршруты API версии 1 группируются под префиксом /api/v1
| и используют соответствующее пространство имен контроллеров
|
| Поддерживаемые форматы:
| - Параметр запроса: ?format=json или ?format=xml
| - Accept заголовок: Accept: application/json или Accept: application/xml
|
*/

Route::prefix('v1')->name('api.v1.')->group(function () {
    
    // Информация о версии API V1
    Route::get('/', function () {
        return response()->json([
            'version' => '1.0.0',
            'description' => 'Blog API Version 1',
            'documentation' => url('/api/documentation'),
            'base_url' => url('/api/v1'),
            'endpoints' => [
                'posts' => [
                    'list' => 'GET /api/v1/posts',
                    'show' => 'GET /api/v1/posts/{id}',
                    'create' => 'POST /api/v1/posts (auth required)',
                    'update' => 'PUT /api/v1/posts/{id} (auth required)',
                    'delete' => 'DELETE /api/v1/posts/{id} (auth required)'
                ],
                'categories' => [
                    'list' => 'GET /api/v1/categories',
                    'show' => 'GET /api/v1/categories/{id}',
                    'create' => 'POST /api/v1/categories (auth required)',
                    'update' => 'PUT /api/v1/categories/{id} (auth required)',
                    'delete' => 'DELETE /api/v1/categories/{id} (auth required)'
                ],
                'tags' => [
                    'list' => 'GET /api/v1/tags',
                    'show' => 'GET /api/v1/tags/{id}',
                    'create' => 'POST /api/v1/tags (auth required)',
                    'update' => 'PUT /api/v1/tags/{id} (auth required)',
                    'delete' => 'DELETE /api/v1/tags/{id} (auth required)'
                ],
                'comments' => [
                    'list' => 'GET /api/v1/comments',
                    'show' => 'GET /api/v1/comments/{id}',
                    'create' => 'POST /api/v1/comments (auth required)',
                    'update' => 'PUT /api/v1/comments/{id} (auth required)',
                    'delete' => 'DELETE /api/v1/comments/{id} (auth required)'
                ]
            ],
            'supported_formats' => ['json', 'xml'],
            'authentication' => 'Laravel Sanctum',
            'examples' => [
                'get_posts_json' => url('/api/v1/posts?format=json'),
                'get_posts_xml' => url('/api/v1/posts?format=xml'),
                'search_posts' => url('/api/v1/posts?search=laravel'),
                'filter_by_category' => url('/api/v1/posts?category_id=1'),
                'paginated_posts' => url('/api/v1/posts?page=1&per_page=5')
            ]
        ]);
    });
    
    /*
    |--------------------------------------------------------------------------
    | Posts API Resource Routes
    |--------------------------------------------------------------------------
    | 
    | Примеры использования:
    | GET /api/v1/posts?format=json          - список постов в JSON
    | GET /api/v1/posts?format=xml           - список постов в XML
    | GET /api/v1/posts/1?format=xml         - конкретный пост в XML
    | 
    | Или через Accept заголовок:
    | GET /api/v1/posts (Accept: application/xml) - список постов в XML
    |
    */
    Route::apiResource('posts', PostController::class);
    
    /*
    |--------------------------------------------------------------------------
    | Categories API Resource Routes  
    |--------------------------------------------------------------------------
    |
    | Примеры использования:
    | GET /api/v1/categories?format=json     - список категорий в JSON
    | GET /api/v1/categories?format=xml      - список категорий в XML
    | GET /api/v1/categories/1?format=xml&include_posts=true - категория с постами в XML
    |
    */
    Route::apiResource('categories', CategoryController::class);
    
    /*
    |--------------------------------------------------------------------------
    | Comments API Resource Routes
    |--------------------------------------------------------------------------
    |
    | Примеры использования:
    | GET /api/v1/comments?format=json       - список комментариев в JSON
    | GET /api/v1/comments?format=xml        - список комментариев в XML
    | GET /api/v1/comments?post_id=1         - комментарии к конкретному посту
    | POST /api/v1/comments                  - создание нового комментария (требует auth)
    | PUT /api/v1/comments/1                 - обновление комментария (требует auth)
    | DELETE /api/v1/comments/1              - удаление комментария (требует auth)
    |
    */
    Route::apiResource('comments', CommentController::class);
    
    /*
    |--------------------------------------------------------------------------
    | Tags API Resource Routes
    |--------------------------------------------------------------------------
    |
    | Примеры использования:
    | GET /api/v1/tags?format=json           - список тегов в JSON
    | GET /api/v1/tags?format=xml            - список тегов в XML
    | GET /api/v1/tags?search=php            - поиск тегов по названию
    | GET /api/v1/tags/1?include_posts=true  - тег с постами
    | POST /api/v1/tags                      - создание нового тега (требует auth)
    | PUT /api/v1/tags/1                     - обновление тега (требует auth)
    | DELETE /api/v1/tags/1                  - удаление тега (требует auth)
    |
    */
    Route::apiResource('tags', TagController::class);
    
    /*
    |--------------------------------------------------------------------------
    | Future API Resources
    |--------------------------------------------------------------------------
    | Здесь можно добавить дополнительные ресурсы:
    */
    
    // Route::apiResource('users', UserController::class);
});

/*
|--------------------------------------------------------------------------
| API V2 Routes (Future)
|--------------------------------------------------------------------------
|

|
*/

// Route::prefix('v2')->name('api.v2.')->group(function () {
//     Route::apiResource('posts', V2\PostController::class);
//     Route::apiResource('categories', V2\CategoryController::class);
//     Route::apiResource('comments', V2\CommentController::class);
//     Route::apiResource('tags', V2\TagController::class);
// });