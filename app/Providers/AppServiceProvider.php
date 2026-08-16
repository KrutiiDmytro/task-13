<?php

namespace App\Providers;

use App\Http\Middleware\AdminMiddleware;
use App\Models\Category;
use App\Models\Tag;
use App\Services\CategoryService;
use App\Services\CommentService;
use App\Services\PostService;
use App\Services\TagService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Регистрируем сервисы
        $this->app->singleton(PostService::class, function () {
            return new PostService;
        });

        $this->app->singleton(CategoryService::class, function () {
            return new CategoryService;
        });

        $this->app->singleton(TagService::class, function () {
            return new TagService;
        });

        $this->app->singleton(CommentService::class, function () {
            return new CommentService;
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

        $this->composeNavigationData();
    }

    /**
     * Подаёт список категорий и популярных тегов в шапку и боковую панель.
     * Это presentation-слой: контроллеры остаются без изменений.
     */
    private function composeNavigationData(): void
    {
        View::composer(['layouts.navigation', 'partials.sidebar'], function ($view) {
            // На свежей установке таблиц ещё нет — тогда просто отдаём пустые списки
            if (! Schema::hasTable('categories')) {
                $view->with(['navCategories' => collect(), 'popularTags' => collect()]);

                return;
            }

            $view->with([
                'navCategories' => Category::query()
                    ->withCount(['posts' => fn ($query) => $query->published()])
                    ->orderBy('name')
                    ->get(),

                // whereHas вместо having: having по подзапросу withCount не работает в SQLite
                'popularTags' => Tag::query()
                    ->withCount('posts')
                    ->whereHas('posts')
                    ->orderByDesc('posts_count')
                    ->take(12)
                    ->get(),
            ]);
        });
    }
}
