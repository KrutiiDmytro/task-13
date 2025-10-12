<?php

namespace Tests\Unit\Providers;

use App\Models\Post;
use App\Policies\PostPolicy;
use App\Providers\AuthServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthServiceProviderTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_has_correct_policies_array(): void
    {
        $provider = new AuthServiceProvider($this->app);

        // Используем рефлексию для проверки protected свойства $policies
        $reflection = new \ReflectionClass($provider);
        $policiesProperty = $reflection->getProperty('policies');
        $policiesProperty->setAccessible(true);
        $policies = $policiesProperty->getValue($provider);

        $this->assertArrayHasKey(Post::class, $policies);
        $this->assertEquals(PostPolicy::class, $policies[Post::class]);
    }

    #[Test]
    public function boot_method_can_be_called(): void
    {
        $provider = new AuthServiceProvider($this->app);

        // Проверяем, что метод boot можно вызвать без ошибок
        $provider->boot();

        // Если мы дошли до этой строки, значит boot() выполнился успешно
        $this->assertTrue(true);
    }

    #[Test]
    public function provider_can_be_instantiated_correctly(): void
    {
        $provider = new AuthServiceProvider($this->app);

        // Проверяем, что провайдер создается корректно
        $this->assertInstanceOf(AuthServiceProvider::class, $provider);

        // Проверяем, что классы политик и моделей существуют
        $this->assertTrue(class_exists(Post::class));
        $this->assertTrue(class_exists(PostPolicy::class));

        // Проверяем, что boot выполняется без ошибок
        $provider->boot();
        $this->assertTrue(true);
    }
}
