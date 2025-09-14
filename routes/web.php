<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

/** Главная: список постов (поиск + фильтры + пагинация) */
Route::get('/', [PostController::class, 'index'])->name('posts.index');

/** Редирект /home -> на главную (удобно для ссылок из шаблонов) */
Route::get('/home', function () {
    return redirect()->route('posts.index');
})->name('home');

/** Dashboard - редирект на логин для неавторизованных */
Route::get('/dashboard', function () {
    return redirect('/login');
})->name('dashboard');

/** Посты — список (доступно всем) */
Route::get('/posts', [PostController::class, 'index'])->name('posts.index');

/** Публичные страницы */
Route::get('/contact', [PublicController::class, 'contactPage'])->name('contact');
Route::get('/news/{id}', [PublicController::class, 'newsDatailsPage'])->name('news.show');
Route::get('/news/{id}/{category}', [PublicController::class, 'newsDatailsPage'])->name('news.show.category');

/** Теги — полный CRUD */
Route::get('/tags', [TagController::class, 'index'])->name('tags.index');
Route::get('/tags/create', [TagController::class, 'create'])->name('tags.create');
Route::post('/tags', [TagController::class, 'store'])->name('tags.store');
Route::get('/tags/{slug}', [TagController::class, 'show'])->name('tags.show');
Route::get('/tags/{tag}/edit', [TagController::class, 'edit'])->name('tags.edit');
Route::put('/tags/{tag}', [TagController::class, 'update'])->name('tags.update');
Route::delete('/tags/{tag}', [TagController::class, 'destroy'])->name('tags.destroy');
Route::post('/tags/ajax', [TagController::class, 'storeAjax'])->name('tags.storeAjax');

/** Комментарии — доступно всем: список / создание / сохранение / просмотр */
Route::resource('comments', CommentController::class)->only([
    'index', 'store', 'create', 'show'
]);

/** Комментарии — редактирование / обновление / удаление (только для авторизованных) */
Route::middleware('auth')->group(function () {
    Route::resource('comments', CommentController::class)->only([
        'edit', 'update', 'destroy'
    ]);
});

/** Посты — создание / редактирование / обновление / удаление (только для авторизованных) */
Route::middleware('auth')->group(function () {
    Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::get('/posts/{post}/edit', [PostController::class, 'edit'])->name('posts.edit');
    Route::put('/posts/{post}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
});

/** Посты — просмотр отдельного поста (доступно всем, должно быть после create/edit) */
Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');

/** Профиль пользователя (только для авторизованных) */
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

/* ------------------------------------------------------------------------
    АДМИН-ПАНЕЛЬ
------------------------------------------------------------------------ */
Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Главная страница админки
        Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])
            ->name('dashboard');

        // CRUD для постов
        Route::resource('posts', \App\Http\Controllers\Admin\PostController::class);

        // CRUD для категорий
        Route::resource('categories', \App\Http\Controllers\Admin\CategoryController::class);

        // CRUD для комментариев
        Route::resource('comments', \App\Http\Controllers\Admin\CommentController::class);

        // CRUD для пользователей
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class);

        // Профиль администратора
        Route::get('/profile', [\App\Http\Controllers\Admin\AdminProfileController::class, 'edit'])
            ->name('profile.edit');
        Route::patch('/profile', [\App\Http\Controllers\Admin\AdminProfileController::class, 'update'])
            ->name('profile.update');
        Route::patch('/profile/password', [\App\Http\Controllers\Admin\AdminProfileController::class, 'updatePassword'])
            ->name('profile.password');
    });