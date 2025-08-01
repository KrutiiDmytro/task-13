<?php

use App\Http\Controllers\PostController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\CommentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Здесь регистрируются веб-маршруты для вашего приложения. Эти
| маршруты загружаются RouteServiceProvider'ом, и все они
| будут назначены группе middleware "web".
|
*/

// Главная страница будет показывать список всех постов.
Route::get('/', [PostController::class, 'index'])->name('home');

// Ресурсный маршрут для постов.
Route::resource('posts', PostController::class);


// Ресурсные маршруты для тегов и комментариев
Route::resource('tags', TagController::class);
Route::resource('comments', CommentController::class);
Route::post('tags/store-ajax', [App\Http\Controllers\TagController::class, 'storeAjax'])->name('tags.store.ajax');

// Админ маршруты (пока оставим, но можно будет удалить, если не планируете использовать)
Route::prefix('admin')->name('admin.')->group(function () {
    Route::resource('tags', TagController::class);
    Route::resource('comments', CommentController::class);
});