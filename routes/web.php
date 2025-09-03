<?php

use Illuminate\Support\Facades\Route;

/* ----- аутентификация/регистрация (Breeze) ------------------------------ */
require __DIR__ . '/auth.php';

/* ----- контроллеры ------------------------------------------------------ */
use App\Http\Controllers\PostController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicController;

/* ------------------------------------------------------------------------
   ТЕСТ / ОТЛАДКА
   ------------------------------------------------------------------------ */
Route::get('/test', function () {
    return view('test');
});

/* ------------------------------------------------------------------------
   ПУБЛИЧНАЯ ЧАСТЬ
   ------------------------------------------------------------------------ */
// ---------------- ТЕГИ: пошук та перегляд ----------------
//  Сторінка списку тегів + пошук по назві тегу (?q=)
Route::get('/tags', [TagController::class, 'index'])->name('tags.index');

// Пости за конкретним тегом
Route::get('/tags/{slug}', [TagController::class, 'show'])->name('tags.show');

/** Главная: список постов (поиск + фильтры + пагинация) */
Route::get('/', [PostController::class, 'index'])->name('posts.index');

/** Редирект /home -> на главную (удобно для ссылок из шаблонов) */
Route::get('/home', function () {
    return redirect()->route('posts.index');
})->name('home');

/** Публичные страницы */
Route::get('/contact', [PublicController::class, 'contactPage'])->name('contact');

/** Детали новости с необязательной категорией */
Route::get('/news/{id}/{category?}', [PublicController::class, 'newsDatailsPage'])
    ->whereNumber('id')
    ->whereNumber('category')
    ->name('news.details');

/** Посты — публично: список / просмотр / создание / сохранение */
Route::resource('posts', PostController::class)->only([
     'show', 'create', 'store'
]);

Route::get('/posts', [PostController::class, 'index']);

/** Теги — список / просмотр */
Route::resource('tags', TagController::class)->only([
    'index', 'show'
]);

/** Комментарии — доступно всем: список / создание / сохранение / просмотр */
Route::resource('comments', CommentController::class)->only([
    'index', 'store', 'create', 'show'
]);

Route::middleware('auth')->group(function () {
    Route::resource('comments', \App\Http\Controllers\CommentController::class)->only([
        'edit', 'update', 'destroy'
    ]);
});

/* ------------------------------------------------------------------------
   АВТОРИЗОВАННЫЕ ПОЛЬЗОВАТЕЛИ
   ------------------------------------------------------------------------ */
Route::middleware('auth')->group(function () {

    /** Посты — только для авторизованных: редактирование/обновление/удаление */
    Route::resource('posts', PostController::class)->only([
        'edit', 'update', 'destroy'
    ]);

    /** Комментарии — редактирование/обновление/удаление (публічної частини) */
    Route::resource('comments', CommentController::class)->only([
        'edit', 'update', 'destroy'
    ]);

    /** Личный кабинет */
    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /** Dashboard (пример: требуется подтверждённый email) */
    Route::view('/dashboard', 'dashboard')->middleware('verified')->name('dashboard');
    });

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
    });

/* ------------------------------------------------------------------------
   ОТЛАДОЧНЫЙ МАРШРУТ (вне админ-группы)
   ------------------------------------------------------------------------ */
Route::middleware('auth')->get('/debug-posts-create', function () {
    return 'Отладочный маршрут работает! Пользователь: ' . (auth()->check() ? auth()->user()->name : 'не авторизован');
});
