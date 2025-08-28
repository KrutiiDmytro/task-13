<?php

/* ----- аутентифікація/реєстрація (Breeze) ------------------------------- */
require __DIR__ . '/auth.php';

/* ----- контролери -------------------------------------------------------- */
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\Admin\DashboardController;                 // Дашборд адмінки
use App\Http\Controllers\Admin\PostController as AdminPostController; // Адмінський контролер постів

/* ------------------------------------------------------------------------
   ТЕСТ / ВІДЛАДКА (не обов'язково)
   ------------------------------------------------------------------------ */
Route::get('/test', function () {
    return view('test');
});

/* =========================
 * ПУБЛІЧНІ МАРШРУТИ
 * ========================= */

//  Головна і список постів доступні за двома шляхами (деякі тести ходять на /posts)
Route::get('/',      [PostController::class, 'index'])->name('posts.index');
Route::get('/posts', [PostController::class, 'index'])->name('posts.index.path');

//  Створення поста — публічно (тести очікують можливість для гостя)
Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
Route::post('/posts',       [PostController::class, 'store'])->name('posts.store');

//  Перегляд одного поста — публічно
Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');

// /home -> на список постів (деякі шаблони посилаються на home)
Route::get('/home', fn () => redirect()->route('posts.index'))->name('home');

//  Теги — список та перегляд за slug (без resource, щоб уникнути конфліктів)
Route::get('/tags',        [TagController::class, 'index'])->name('tags.index');
Route::get('/tags/{slug}', [TagController::class, 'show'])->name('tags.show');

//  Публічні сторінки (пошук/категорія/теґ)
Route::get('/search',          [PublicController::class, 'search'])->name('search');
Route::get('/category/{slug}', [PublicController::class, 'byCategory'])->name('category.show');
Route::get('/tag/{slug}',      [PublicController::class, 'byTag'])->name('tag.show');

//  Приклад додаткових публічних сторінок
Route::get('/contact', [PublicController::class, 'contactPage'])->name('contact');
Route::get('/news/{id}/{category?}', [PublicController::class, 'newsDatailsPage']) // TODO: назва методу в контролері: newsDatailsPage чи newsDetailsPage?
    ->whereNumber('id')
    ->whereNumber('category')
    ->name('news.details');

/* =========================
 * КОМЕНТАРІ
 * ========================= */

//  Коментарі — публічні дії: список/створення/перегляд/збереження
Route::resource('comments', CommentController::class)->only([
    'index', 'create', 'store', 'show'
]);

/* =========================
 * ЗАХИЩЕНІ МАРШРУТИ (AUTH)
 * ========================= */

Route::middleware('auth')->group(function () {
    //  Пост — редагування/оновлення/видалення (власник або адмін — додаткова перевірка у контролері)
    Route::resource('posts', PostController::class)->only([
        'edit', 'update', 'destroy'
    ]);

    //  Коментарі — редагування/оновлення/видалення
    Route::resource('comments', CommentController::class)->only([
        'edit', 'update', 'destroy'
    ]);

    //  Профіль
    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Dashboard (приклад: потребує підтвердження email)
    Route::view('/dashboard', 'dashboard')->middleware('verified')->name('dashboard');
});

/* =========================
 * АДМІН-ПАНЕЛЬ (AUTH + ADMIN)
 * ========================= */

// Всі маршрути під /admin захищені 'auth' + 'admin', іменування починається з 'admin.'
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Мінімальні роути
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    //  Повний ресурс постів в адмінці → створює admin.posts.index/show/create/store/edit/update/destroy
    Route::resource('posts', AdminPostController::class);

    //  Приклади повних CRUD у адмінці (якщо реалізовані контролери)
    Route::resource('categories', \App\Http\Controllers\Admin\CategoryController::class);
    Route::resource('comments',   \App\Http\Controllers\Admin\CommentController::class);
    Route::resource('users',      \App\Http\Controllers\Admin\UserController::class);
});

/* =========================
 * ВІДЛАДКА (AUTH)
 * ========================= */

Route::middleware('auth')->get('/debug-posts-create', function () {
    return 'Отладочный маршрут работает! Пользователь: ' . (auth()->check() ? auth()->user()->name : 'не авторизован');
});
