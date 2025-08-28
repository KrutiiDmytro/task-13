<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use App\Services\PostService;
use App\Services\CategoryService;
use App\Services\TagService;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\AdminMiddleware;

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