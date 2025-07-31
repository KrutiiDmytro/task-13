<?php

use App\Http\Controllers\PostController;
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
// Laravel автоматически создаст все необходимые маршруты:
// GET /posts -> index() - список всех постов
// GET /posts/create -> create() - форма создания
// POST /posts -> store() - сохранение нового поста
// GET /posts/{post} -> show() - просмотр одного поста
// GET /posts/{post}/edit -> edit() - форма редактирования
// PUT/PATCH /posts/{post} -> update() - обновление поста
// DELETE /posts/{post} -> destroy() - удаление поста
Route::resource('posts', PostController::class);
