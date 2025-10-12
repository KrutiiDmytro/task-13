<?php

namespace App\Providers;

use App\Http\Middleware\AdminMiddleware;
use App\Services\CategoryService;
use App\Services\CommentService;
use App\Services\PostService;
use App\Services\TagService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Регистрируем сервисы
        $this->app->singleton(PostService::class, function ($app) {
            return new PostService();
        });

        $this->app->singleton(CategoryService::class, function ($app) {
            return new CategoryService();
        });

        $this->app->singleton(TagService::class, function ($app) {
            return new TagService();
        });

        $this->app->singleton(CommentService::class, function ($app) {
            return new CommentService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // реєстрація кастомного middleware під псевдонімом 'admin'
        Route::aliasMiddleware('admin', AdminMiddleware::class);

        Paginator::useBootstrapFive();
    }
}
