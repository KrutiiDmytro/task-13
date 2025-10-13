<?php

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CommentController as AdminCommentController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

// Публичные роуты через PublicController
Route::get('/', [PublicController::class, 'index'])->name('home');
Route::get('/search', [PublicController::class, 'search'])->name('public.search');
Route::get('/category/{slug}', [PublicController::class, 'byCategory'])->name('public.category');
Route::get('/tag/{slug}', [PublicController::class, 'byTag'])->name('public.tag');
Route::get('/post/{slug}', [PublicController::class, 'show'])->name('public.post');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/auth.php';

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('posts', AdminPostController::class);
    Route::resource('categories', AdminCategoryController::class);
    Route::resource('comments', AdminCommentController::class);
    Route::resource('users', AdminUserController::class);
});

// Публичные маршруты
Route::resource('posts', PostController::class);
Route::resource('comments', CommentController::class);
Route::resource('tags', TagController::class);

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Маршруты для Swagger UI
Route::get('/api/documentation', function () {
    return view('l5-swagger::index', [
        'documentation' => 'default',
        'documentationTitle' => 'Blog API Documentation',
        'urlsToDocs' => [
            'Blog API' => url('/api/docs.json'),
        ],
        'useAbsolutePath' => true,
        'operationsSorter' => config('l5-swagger.defaults.ui.operationsSorter', null),
        'configUrl' => config('l5-swagger.defaults.ui.configUrl', null),
        'validatorUrl' => config('l5-swagger.defaults.ui.validatorUrl', null),
        'additionalConfigUrl' => null,
        'requestInterceptor' => null,
        'responseInterceptor' => null,
    ]);
})->name('l5-swagger.default.docs');

Route::get('/api/docs.json', function () {
    $filePath = storage_path('api-docs/api-docs.json');
    if (file_exists($filePath)) {
        return response()->file($filePath, [
            'Content-Type' => 'application/json',
        ]);
    }

    return response()->json(['error' => 'Documentation not found'], 404);
})->name('l5-swagger.default.api');

// Добавим маршрут для OAuth2 callback (требуется для Swagger UI)
Route::get('/api/oauth2-redirect.html', function () {
    return response('<!DOCTYPE html><html><head><title>Swagger UI: OAuth2 Redirect</title></head><body></body></html>');
})->name('l5-swagger.default.oauth2_callback');

// Маршрут для редиректа на API документацию
Route::get('/docs', function () {
    return redirect('/api/documentation');
})->name('api.docs');

// Информация об API
Route::get('/api-info', function () {
    return response()->json([
        'api_name' => 'Blog API',
        'version' => '1.0.0',
        'description' => 'REST API для системы управления блогом',
        'documentation_url' => url('/api/documentation'),
        'base_url' => url('/api/v1'),
        'supported_formats' => ['json', 'xml'],
        'authentication' => 'Laravel Sanctum',
        'endpoints' => [
            'posts' => url('/api/v1/posts'),
            'categories' => url('/api/v1/categories'),
            'comments' => url('/api/v1/comments'),
            'tags' => url('/api/v1/tags'),
        ],
    ]);
})->name('api.info');
