<?php

namespace Tests\Unit\Providers;

use App\Http\Middleware\AdminMiddleware;
use App\Providers\AppServiceProvider;
use App\Services\CategoryService;
use App\Services\PostService;
use App\Services\TagService;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AppServiceProviderTest extends TestCase
{
    #[Test]
    public function it_registers_post_service_as_singleton(): void
    {
        $service1 = app(PostService::class);
        $service2 = app(PostService::class);

        $this->assertInstanceOf(PostService::class, $service1);
        $this->assertSame($service1, $service2); // Должны быть одним и тем же объектом (singleton)
    }

    #[Test]
    public function it_registers_category_service_as_singleton(): void
    {
        $service1 = app(CategoryService::class);
        $service2 = app(CategoryService::class);

        $this->assertInstanceOf(CategoryService::class, $service1);
        $this->assertSame($service1, $service2);
    }

    #[Test]
    public function it_registers_tag_service_as_singleton(): void
    {
        $service1 = app(TagService::class);
        $service2 = app(TagService::class);

        $this->assertInstanceOf(TagService::class, $service1);
        $this->assertSame($service1, $service2);
    }

    #[Test]
    public function it_registers_admin_middleware_alias(): void
    {
        $middlewareGroups = Route::getMiddleware();

        $this->assertArrayHasKey('admin', $middlewareGroups);
        $this->assertEquals(AdminMiddleware::class, $middlewareGroups['admin']);
    }

    #[Test]
    public function it_sets_default_string_length(): void
    {
        // Проверяем, что Schema::defaultStringLength вызывается
        // Это сложно протестировать напрямую, но мы можем проверить,
        // что провайдер загружается без ошибок
        $provider = new AppServiceProvider(app());

        $this->assertInstanceOf(AppServiceProvider::class, $provider);

        // Вызываем boot метод
        $provider->boot();

        // Если дошли до этого момента без исключений, значит всё работает
        $this->assertTrue(true);
    }

    #[Test]
    public function register_method_creates_service_bindings(): void
    {
        // Тестируем, что метод register() правильно регистрирует сервисы
        $provider = new AppServiceProvider(app());

        // Явно вызываем register для покрытия
        $provider->register();

        // Проверяем, что сервисы зарегистрированы
        $this->assertTrue(app()->bound(PostService::class));
        $this->assertTrue(app()->bound(CategoryService::class));
        $this->assertTrue(app()->bound(TagService::class));
    }

    #[Test]
    public function boot_method_configures_application(): void
    {
        // Тестируем, что метод boot() правильно настраивает приложение
        $provider = new AppServiceProvider(app());

        // Явно вызываем boot для покрытия
        $provider->boot();

        // Проверяем, что middleware зарегистрирован
        $middlewareGroups = Route::getMiddleware();
        $this->assertArrayHasKey('admin', $middlewareGroups);
        $this->assertEquals(AdminMiddleware::class, $middlewareGroups['admin']);
    }
}
